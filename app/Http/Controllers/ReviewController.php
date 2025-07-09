<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Hotel;
use App\Models\Property;
use App\Services\ReviewService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponses;
    public function __construct(
        private ReviewService $reviewService
    ) {}
    public function addReview(Request $request)
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
    public function getItemReviews(Request $request)
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
    public function getReviewStats(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer',
            'service_type' => 'nullable|in:hire,purchase,rent,accommodation',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $stats = $this->reviewService->getReviewStats($item, $request->service_type);

        if ($stats['success']) {
            return $this->ok('Review stats retrieved', $stats['data']);
        } else {
            return $this->error('Failed to get review stats', $stats['message']);
        }
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request)
    {
        $serviceType = $request->get('service_type');
        $reviews = $this->reviewService->getUserReviewsPaginated(auth()->user(), 15, $serviceType);

        return $this->ok('User reviews retrieved', $reviews);
    }

    /**
     * Delete a review
     */
    public function deleteReview(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:car,property,hotel',
            'item_id' => 'required|integer',
            'service_type' => 'required|in:hire,purchase,rent,accommodation',
        ]);

        $item = $this->getItemModel($request->item_type, $request->item_id);
        $result = $this->reviewService->deleteReview(auth()->user(), $item, $request->service_type);

        if ($result['success']) {
            return $this->ok('Review deleted');
        } else {
            return $this->error('Failed to delete review', $result['message']);
        }
    }

    /**
     * Helper method to get model instance
     */
    private function getItemModel(string $type, int $id)
    {
        return match ($type) {
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
        return match ($type) {
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
        return match ($type) {
            'car' => 'cars',
            'property' => 'properties',
            'hotel' => 'hotels',
            default => throw new \InvalidArgumentException("Invalid item type: {$type}")
        };
    }
}
