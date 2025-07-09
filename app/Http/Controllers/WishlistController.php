<?php

namespace App\Http\Controllers;

use App\Http\Resources\CarResource;
use App\Http\Resources\HotelResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\WishlistResource;
use App\Models\Car;
use App\Models\Hotel;
use App\Models\Property;
use App\Services\ReviewService;
use App\Services\WishlistService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService,
        private ReviewService $reviewService
    ) {}
    use ApiResponses;

    public function addToWishlist(Request $request): JsonResponse
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer|exists:' . $this->getTableName($request->item_type) . ',id',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $result = $this->wishlistService->addToWishlist(auth()->user(), $item);
        if ($result['success']) {
            // You can check the type of the item and return the appropriate resource or response.
            // For example:
            $wishlistItem = $result['data'];
            $wishlistable = $wishlistItem->wishlistable;

            if ($wishlistable instanceof Car) {
                $wishlistItem->wishlistable =  new CarResource($wishlistable);
            } elseif ($wishlistable instanceof Property) {
                $wishlistItem->wishlistable =  new PropertyResource($wishlistable);
            } elseif ($wishlistable instanceof Hotel) {
                $wishlistItem->wishlistable =  new HotelResource($wishlistable);
            }

            // Or just return the wishlist item with its loaded relation for now:
            return $this->ok('Item added to wishlist', new WishlistResource($wishlistItem));
        } else {
            return $this->error('Failed to add item to wishlist', $result['message']);
        }
    }
    /**
     * Remove item from wishlist
     */
    public function removeFromWishlist(Request $request): JsonResponse
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $result = $this->wishlistService->removeFromWishlist(auth()->user(), $item);
        if ($result['success']) {
            return $this->ok('Item removed from wishlist');
        } else {
            return $this->error('Failed to remove item from wishlist', $result['message']);
        }
    }

    /**
     * Toggle wishlist item
     */
    public function toggleWishlist(Request $request): JsonResponse
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $result = $this->wishlistService->toggleWishlist(auth()->user(), $item);
        if ($result['success']) {
            return $this->ok('Item toggled from wishlist');
        } else {
            return $this->error('Failed to toggle item from wishlist', $result['message']);
        }
    }

    /**
     * Get user's wishlist
     */
    public function getUserWishlist(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $wishlist = $this->wishlistService->getUserWishlistPaginated(auth()->user(), 15, $type);
        // dd($wishlist);
        return $this->ok('User wishlist retrieved', $wishlist);
    }

    /**
     * Add a review
     */
    public function addReview(Request $request): JsonResponse
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer|exists:' . $this->getTableName($request->item_type) . ',id',
            'service_type' => 'required|in:hire,purchase,rent,accommodation',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'is_verified' => 'boolean',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        
        $result = $this->reviewService->addReview(auth()->user(), $item, [
            'service_type' => $request->service_type,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified' => $request->is_verified ?? false,
        ]);

        if ($result['success']) {
            return $this->ok('Review added', $result['data']);
        } else {
            return $this->error('Failed to add review', $result['message']);
        }
    }

    /**
     * Get reviews for an item
     */
    public function getItemReviews(Request $request): JsonResponse
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer',
            'service_type' => 'nullable|in:hire,purchase,rent,accommodation',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $reviews = $this->reviewService->getItemReviewsPaginated($item, 15, $request->service_type);

        return $this->ok('Item reviews retrieved', $reviews);
    }

    /**
     * Get review statistics for an item
     */
    public function getReviewStats(Request $request): JsonResponse
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer',
            'service_type' => 'nullable|in:hire,purchase,rent,accommodation',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $stats = $this->reviewService->getReviewStats($item, $request->service_type);

        return $this->ok('Review stats retrieved', $stats);
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request): JsonResponse
    {
        $serviceType = $request->get('service_type');
        $reviews = $this->reviewService->getUserReviewsPaginated(auth()->user(), 15, $serviceType);

        return $this->ok('User reviews retrieved', $reviews);
    }

    /**
     * Delete a review
     */
    public function deleteReview(Request $request): JsonResponse
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer',
            'service_type' => 'required|in:hire,purchase,rent,accommodation',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $result = $this->reviewService->deleteReview(auth()->user(), $item, $request->service_type);

        if ($result['success']) {
            return $this->ok('Review deleted', $result['data']);
        } else {
            return $this->error('Failed to delete review', $result['message']);
        }
    }

    /**
     * Get popular items
     */
    public function getPopularItems(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:car,property,hotel',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $modelClass = $this->getModelClass($request->type);
        $items = $this->wishlistService->getPopularItems($modelClass, $request->limit ?? 10);

        return $this->ok('Popular items retrieved', $items);
    }

    /**
     * Get top-rated items
     */
    public function getTopRatedItems(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:car,property,hotel',
            'service_type' => 'nullable|in:hire,purchase,rent,accommodation',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $modelClass = $this->getModelClass($request->type);
        $items = $this->reviewService->getTopRatedItems(
            $modelClass, 
            $request->limit ?? 10, 
            $request->service_type
        );

        return $this->ok('Top rated items retrieved', $items);
    }
    /**
     * Helper method to get model instance
     */
    private function getItemModel(string $type, int $id)
    {
        return match($type) {
            'car' => Car::findOrFail($id),
            'property' => Property::findOrFail($id),
            'hotel' => Hotel::findOrFail($id),
            default => throw new \InvalidArgumentException("Invalid item type: {$type}")
        };
    }

    /**
     * Helper method to get model class
     */
    private function getModelClass(string $type): string
    {
        return match($type) {
            'car' => Car::class,
            'property' => Property::class,
            'hotel' => Hotel::class,
            default => throw new \InvalidArgumentException("Invalid item type: {$type}")
        };
    }

    /**
     * Helper method to get table name
     */
    private function getTableName(string $type): string
    {
        return match($type) {
            'car' => 'cars',
            'property' => 'properties',
            'hotel' => 'hotels',
            default => throw new \InvalidArgumentException("Invalid item type: {$type}")
        };
    }
}
