<?php

namespace App\Services;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class ReviewService
{
    /**
     * Valid service types
     */
    public const SERVICE_TYPES = [
        'hire' => 'Car Hire',
        'purchase' => 'Property Purchase',
        'rent' => 'Property Rent',
        'accommodation' => 'Hotel Accommodation'
    ];

    /**
     * Add or update a review
     */
    public function addReview(User $user, Model $item, array $data): array
    {
        try {
            DB::beginTransaction();
            
            // Validate service type
            if (!array_key_exists($data['service_type'], self::SERVICE_TYPES)) {
                return [
                    'success' => false,
                    'message' => 'Invalid service type provided',
                    'data' => null
                ];
            }

            // Validate rating
            if (!isset($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
                return [
                    'success' => false,
                    'message' => 'Rating must be between 1 and 5',
                    'data' => null
                ];
            }

            $review = Review::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'reviewable_type' => get_class($item),
                    'reviewable_id' => $item->id,
                    'service_type' => $data['service_type'],
                ],
                [
                    'rating' => $data['rating'],
                    'comment' => $data['comment'] ?? null,
                    'is_verified' => $data['is_verified'] ?? false,
                ]
            );
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $review->wasRecentlyCreated ? 'Review added successfully' : 'Review updated successfully',
                'data' => $review->load('user'),
                'was_created' => $review->wasRecentlyCreated
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Failed to add review: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get reviews for a specific item
     */
    public function getItemReviews(Model $item, ?string $serviceType = null): Collection
    {
        $query = Review::where([
            'reviewable_type' => get_class($item),
            'reviewable_id' => $item->id,
        ])->with('user');
        
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        return $query->latest()->get();
    }

    /**
     * Get paginated reviews for a specific item
     */
    public function getItemReviewsPaginated(Model $item, int $perPage = 15, ?string $serviceType = null)
    {
        $query = Review::where([
            'reviewable_type' => get_class($item),
            'reviewable_id' => $item->id,
        ])->with('user');
        
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(User $user, ?string $serviceType = null): Collection
    {
        $query = Review::where('user_id', $user->id)
            ->with('reviewable');
        
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        return $query->latest()->get();
    }

    /**
     * Get user's reviews with pagination
     */
    public function getUserReviewsPaginated(User $user, int $perPage = 15, ?string $serviceType = null)
    {
        $query = Review::where('user_id', $user->id)
            ->with('reviewable');
        
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get review statistics for an item
     */
    public function getReviewStats(Model $item, ?string $serviceType = null): array
    {
        $query = Review::where([
            'reviewable_type' => get_class($item),
            'reviewable_id' => $item->id,
        ]);
        
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        $reviews = $query->get();
        
        if ($reviews->isEmpty()) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'verified_reviews' => 0,
                'service_type_breakdown' => []
            ];
        }
        
        $ratingDistribution = $reviews->countBy('rating')->toArray();
        $ratingDistribution = array_merge([1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0], $ratingDistribution);
        
        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => round($reviews->avg('rating'), 1),
            'rating_distribution' => $ratingDistribution,
            'verified_reviews' => $reviews->where('is_verified', true)->count(),
            'service_type_breakdown' => $reviews->countBy('service_type')->toArray()
        ];
    }

    /**
     * Check if user has reviewed an item for a specific service
     */
    public function hasUserReviewed(User $user, Model $item, string $serviceType): bool
    {
        return Review::where([
            'user_id' => $user->id,
            'reviewable_type' => get_class($item),
            'reviewable_id' => $item->id,
            'service_type' => $serviceType,
        ])->exists();
    }

    /**
     * Delete a review
     */
    public function deleteReview(User $user, Model $item, string $serviceType): array
    {
        try {
            $deleted = Review::where([
                'user_id' => $user->id,
                'reviewable_type' => get_class($item),
                'reviewable_id' => $item->id,
                'service_type' => $serviceType,
            ])->delete();
            
            return [
                'success' => true,
                'message' => $deleted ? 'Review deleted successfully' : 'Review not found',
                'was_deleted' => $deleted > 0
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete review: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent reviews across all items
     */
    public function getRecentReviews(int $limit = 10, ?string $serviceType = null): Collection
    {
        $query = Review::with(['user', 'reviewable']);
        
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        return $query->latest()->limit($limit)->get();
    }

    /**
     * Get top-rated items by type
     */
    public function getTopRatedItems(string $itemType, int $limit = 10, ?string $serviceType = null): Collection
    {
        $query = Review::where('reviewable_type', $itemType)
            ->with('reviewable')
            ->select('reviewable_id', 'reviewable_type', DB::raw('AVG(rating) as avg_rating'), DB::raw('COUNT(*) as review_count'))
            ->groupBy('reviewable_id', 'reviewable_type')
            ->having('review_count', '>=', 3); // Minimum 3 reviews
        
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        return $query->orderBy('avg_rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get filtered reviews
     */
    public function getFilteredReviews(array $filters = []): Collection
    {
        $query = Review::with(['user', 'reviewable']);
        
        if (isset($filters['item_type'])) {
            $query->where('reviewable_type', $filters['item_type']);
        }
        
        if (isset($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }
        
        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }
        
        if (isset($filters['verified_only']) && $filters['verified_only']) {
            $query->where('is_verified', true);
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
     * Mark review as verified
     */
    public function markAsVerified(Review $review): array
    {
        try {
            $review->update(['is_verified' => true]);
            
            return [
                'success' => true,
                'message' => 'Review marked as verified',
                'data' => $review
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to verify review: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available service types
     */
    public function getServiceTypes(): array
    {
        return self::SERVICE_TYPES;
    }
}