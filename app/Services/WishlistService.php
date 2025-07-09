<?php

namespace App\Services;

use App\Http\Resources\CarResource;
use App\Http\Resources\HotelResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\WishlistResource;
use App\Models\Car;
use App\Models\Hotel;
use App\Models\Property;
use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class WishlistService
{
    /**
     * Add item to user's wishlist
     */
    public function addToWishlist(User $user, Model $item): array
    {
        try {
            DB::beginTransaction();
            
            $wishlist = Wishlist::firstOrCreate([
                'user_id' => $user->id,
                'wishlistable_type' => get_class($item),
                'wishlistable_id' => $item->id,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $wishlist->wasRecentlyCreated ? 'Item added to wishlist' : 'Item already in wishlist',
                'data' => $wishlist,
                'was_added' => $wishlist->wasRecentlyCreated
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Failed to add item to wishlist: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Remove item from user's wishlist
     */
    public function removeFromWishlist(User $user, Model $item): array
    {
        try {
            $deleted = Wishlist::where([
                'user_id' => $user->id,
                'wishlistable_type' => get_class($item),
                'wishlistable_id' => $item->id,
            ])->delete();
            
            return [
                'success' => true,
                'message' => $deleted ? 'Item removed from wishlist' : 'Item not found in wishlist',
                'was_removed' => $deleted > 0
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to remove item from wishlist: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Toggle item in user's wishlist
     */
    public function toggleWishlist(User $user, Model $item): array
    {
        $exists = $this->isInWishlist($user, $item);
        
        if ($exists) {
            return $this->removeFromWishlist($user, $item);
        } else {
            return $this->addToWishlist($user, $item);
        }
    }

    /**
     * Check if item is in user's wishlist
     */
    public function isInWishlist(User $user, Model $item): bool
    {
        return Wishlist::where([
            'user_id' => $user->id,
            'wishlistable_type' => get_class($item),
            'wishlistable_id' => $item->id,
        ])->exists();
    }

    /**
     * Get user's wishlist items
     */
    public function getUserWishlist(User $user, ?string $type = null): Collection
    {
        $query = Wishlist::where('user_id', $user->id)
            ->with('wishlistable');
        
        if ($type) {
            $query->where('wishlistable_type', $type);
        }
        
        return $query->latest()->get();
    }

    /**
     * Get user's wishlist with pagination
     */
    public function getUserWishlistPaginated(User $user, int $perPage = 15, ?string $type = null)
    {
        $query = Wishlist::where('user_id', $user->id)
            ->with('wishlistable');
        
        if ($type) {
            $query->where('wishlistable_type', $type);
        }
        // You can determine which resource to use by checking the class of the wishlistable model.
        // For example, after retrieving the paginated wishlist, you can map over the items and wrap them in the appropriate resource:
        //
        // use App\Http\Resources\CarResource;
        // use App\Http\Resources\PropertyResource;
        // use App\Http\Resources\HotelResource;
        //
        $paginated = $query->latest()->paginate($perPage);
        $paginated->getCollection()->transform(function ($wishlistItem) {
            $wishlistable = $wishlistItem->wishlistable;
            if ($wishlistable instanceof Car) {
                $wishlistItem->wishlistable = new CarResource($wishlistable);
            } elseif ($wishlistable instanceof Property) {
                $wishlistItem->wishlistable = new PropertyResource($wishlistable);
            } elseif ($wishlistable instanceof Hotel) {
                $wishlistItem->wishlistable = new HotelResource($wishlistable);
            }
            return new WishlistResource($wishlistItem);
        });
        return $paginated;
        //
        // Alternatively, you can do this mapping in your controller after calling this service method.
        // return $query->latest()->paginate($perPage);
    }

    /**
     * Get wishlist count by type for user
     */
    public function getWishlistCountByType(User $user): array
    {
        return Wishlist::where('user_id', $user->id)
            ->select('wishlistable_type', DB::raw('count(*) as count'))
            ->groupBy('wishlistable_type')
            ->pluck('count', 'wishlistable_type')
            ->toArray();
    }

    /**
     * Clear user's entire wishlist
     */
    public function clearWishlist(User $user, ?string $type = null): array
    {
        try {
            $query = Wishlist::where('user_id', $user->id);
            
            if ($type) {
                $query->where('wishlistable_type', $type);
            }
            
            $deleted = $query->delete();
            
            return [
                'success' => true,
                'message' => "Removed {$deleted} items from wishlist",
                'deleted_count' => $deleted
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to clear wishlist: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get wishlist items with additional filtering
     */
    public function getFilteredWishlist(User $user, array $filters = []): Collection
    {
        $query = Wishlist::where('user_id', $user->id)
            ->with('wishlistable');
        
        if (isset($filters['type'])) {
            $query->where('wishlistable_type', $filters['type']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->latest()->get();
    }

    /**
     * Get popular wishlist items (most wishlisted)
     */
    public function getPopularItems(string $type, int $limit = 10): Collection
    {
        return Wishlist::where('wishlistable_type', $type)
            ->with('wishlistable')
            ->select('wishlistable_id', 'wishlistable_type', DB::raw('count(*) as wishlist_count'))
            ->groupBy('wishlistable_id', 'wishlistable_type')
            ->orderBy('wishlist_count', 'desc')
            ->limit($limit)
            ->get();
    }
}