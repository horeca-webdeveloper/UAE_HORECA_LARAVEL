<?php 


// namespace App\Http\Controllers\API;

// use App\Http\Controllers\Controller;
// use Botble\Ecommerce\Models\Review; // Assuming the review model is located here
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class UserReviewApiController extends Controller
// {
//     /**
//      * Get all reviews for the logged-in customer
//      *
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function getCustomerReviews()
//     {
//         $userId = Auth::id(); // Get the authenticated user

//         if (!$userId) {
//             return response()->json(['message' => 'User not authenticated.'], 401);
//         }

//         // Fetch reviews for the logged-in customer
//         $reviews = Review::where('customer_id', $userId)
//             ->with('product') // Eager load product details
//             ->get(); // You can also paginate if needed

//         // Check if reviews exist
//         if ($reviews->isEmpty()) {
//             return response()->json(['message' => 'No reviews found for this user.'], 404);
//         }

//         // Return reviews with product data
//         return response()->json($reviews);
//     }

  

//     public function createReview(Request $request)
//     {
//         $userId = Auth::id();
    
//         if (!$userId) {
//             return response()->json(['message' => 'User not authenticated.', 'success' => false], 401);
//         }
    
//         // Validate request
//         $request->validate([
//             'product_id' => 'required|exists:ec_products,id',
//             'star' => 'required|integer|min:1|max:5',
//             'comment' => 'required|string',
//             'images' => 'nullable|array',
//             'images.*' => 'file|mimes:jpeg,png,jpg,gif|max:2048',
//         ]);
    
//         // Check if the user already submitted a review for this product
//         $existingReview = Review::where('customer_id', $userId)
//             ->where('product_id', $request->product_id)
//             ->first();
    
//         if ($existingReview) {
//             return response()->json([
//                 'message' => 'You have already submitted a review for this product.',
//                 'success' => true,
//                 'review' => $existingReview,
//             ], 200);
//         }
    
//         // Handle file uploads
//         $imageUrls = [];
//         try {
//             if ($request->has('images')) {
//                 foreach ($request->file('images') as $image) {
//                     $path = $image->storeAs('public/storage/products', $image->getClientOriginalName());
//                     $imageUrls[] = asset("storage/products/{$image->getClientOriginalName()}");
//                 }
//             }
    
//             // Create the review with status set to "published"
//             $review = Review::create([
//                 'customer_id' => $userId,
//                 'customer_name' => Auth::user()->name,
//                 'product_id' => $request->product_id,
//                 'star' => $request->star,
//                 'comment' => $request->comment,
//                 'status' => 'published', // Automatically set to published
//                 'images' => !empty($imageUrls) ? json_encode($imageUrls) : null,
//             ]);
    
//             if ($review) {
//                 return response()->json([
//                     'message' => 'Review successfully added',
//                     'success' => true,
//                     'review' => $review,
//                 ], 201);
//             }
    
//             return response()->json(['message' => 'Review failed', 'success' => false], 500);
    
//         } catch (\Exception $e) {
//             return response()->json(['message' => 'Error occurred: ' . $e->getMessage(), 'success' => false], 500);
//         }
//     }
    

//     /**
//      * Update a specific review
//      *
//      * @param \Illuminate\Http\Request $request
//      * @param int $id
//      * @return \Illuminate\Http\JsonResponse
//      */
//     // public function updateReview(Request $request, $id)
//     // {
//     //     $userId = Auth::id();

//     //     if (!$userId) {
//     //         return response()->json(['message' => 'User not authenticated.'], 401);
//     //     }

//     //     // Find the review by ID
//     //     $review = Review::where('id', $id)->where('customer_id', $userId)->first();

//     //     if (!$review) {
//     //         return response()->json(['message' => 'Review not found or unauthorized.'], 404);
//     //     }

//     //     // Validate the incoming request
//     //     $request->validate([
//     //         'star' => 'nullable|integer|min:1|max:5',
//     //         'comment' => 'nullable|string',
//     //         'images' => 'nullable|array',
//     //         'images.*' => 'url', // Ensure each image is a valid URL
//     //     ]);

//     //     // Update the review
//     //     $review->update($request->only(['star', 'comment', 'images']));

//     //     return response()->json(['message' => 'Review updated successfully.', 'review' => $review]);
//     // }
    
//     /**
//  * Update a specific review
//  *
//  * @param \Illuminate\Http\Request $request
//  * @param int $id
//  * @return \Illuminate\Http\JsonResponse
//  */

//     public function updateReview(Request $request, $id)
//     {
//         $userId = Auth::id(); // Get the authenticated user's ID

//         if (!$userId) {
//             return response()->json(['message' => 'User not authenticated.'], 401);
//         }

//         // Find the review by ID
//         $review = Review::where('id', $id)->where('customer_id', $userId)->first();

//         if (!$review) {
//             return response()->json(['message' => 'Review not found or unauthorized.'], 404);
//         }

//         // Validate incoming request
//         $request->validate([
//             'star' => 'nullable|integer|min:1|max:5',
//             'comment' => 'nullable|string',
//             'images' => 'nullable|array',
//             'images.*' => 'file|mimes:jpeg,png,jpg,gif|max:2048',
//         ]);

//         $uploadedImagePaths = [];
        
//         if ($request->hasFile('images')) {
//             foreach ($request->file('images') as $image) {
//                 $filename = uniqid() . '_' . $image->getClientOriginalName();
//                 $image->move(public_path('storage'), $filename);

//                 $uploadedImagePaths[] = asset('storage/' . $filename);
//             }
//         }

//         $dataToUpdate = $request->only(['star', 'comment']);
//         if (!empty($uploadedImagePaths)) {
//             $dataToUpdate['images'] = $uploadedImagePaths;
//         }

//         $review->update($dataToUpdate);

//         return response()->json([
//             'success' => true,
//             'message' => 'Review updated successfully.',
//             'review' => $review,
//         ]);
//     }




//     /**
//      * Delete a specific review
//      *
//      * @param int $id
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function deleteReview($id)
//     {
//         $userId = Auth::id();

//         if (!$userId) {
//             return response()->json(['message' => 'User not authenticated.'], 401);
//         }

//         // Find the review by ID
//         $review = Review::where('id', $id)->where('customer_id', $userId)->first();

//         if (!$review) {
//             return response()->json(['message' => 'Review not found or unauthorized.'], 404);
//         }

//         // Delete the review
//         $review->delete();

//         return response()->json(['message' => 'Review deleted successfully.']);
//     }
// }



namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Ecommerce\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class UserReviewApiController extends Controller
{
    protected $storagePath = 'storage/sssp-1/';

    /**
     * Get all reviews for the logged-in customer
     */
    public function getCustomerReviews()
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $reviews = Review::where('customer_id', $userId)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reviews->isEmpty()) {
            return response()->json(['message' => 'No reviews found for this user.'], 404);
        }

        // Transform images to full URLs
        $reviews->transform(function ($review) {
            $review->images = json_decode($review->images, true);
            if ($review->images) {
                $review->images = array_map(function ($image) {
                    return url($image);
                }, $review->images);
            }
            return $review;
        });

        return response()->json($reviews);
    }

   
    /**
     * Create a new review
     */
    public function createReview(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Debug the request
        \Log::info('Review Creation Request', [
            'has_files' => $request->hasFile('images'),
            'all_data' => $request->all(),
            'files' => $request->files->all()
        ]);

        // Validate the request
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'star' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Check for existing review
        $existingReview = Review::where('customer_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'You have already submitted a review for this product.'], 400);
        }

        $imagePaths = [];
        try {
            // Check if images were sent in the request
            if ($request->hasFile('images')) {
                \Log::info('Processing images', ['count' => count($request->file('images'))]);
                
                // Ensure directory exists
                if (!File::isDirectory(public_path($this->storagePath))) {
                    File::makeDirectory(public_path($this->storagePath), 0777, true);
                }

                foreach ($request->file('images') as $image) {
                    // Debug image information
                    \Log::info('Processing image', [
                        'original_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getMimeType(),
                        'size' => $image->getSize()
                    ]);

                    $filename = time() . '_' . $image->getClientOriginalName();
                    $path = $this->storagePath . $filename;
                    
                    // Move the file
                    if ($image->move(public_path($this->storagePath), $filename)) {
                        $imagePaths[] = $path;
                        \Log::info('Image saved successfully', ['path' => $path]);
                    } else {
                        \Log::error('Failed to move image', ['filename' => $filename]);
                    }
                }
            }

            // Create the review
            $review = Review::create([
                'customer_id' => $userId,
                'customer_name' => Auth::user()->name,
                'product_id' => $request->product_id,
                'star' => $request->star,
                'comment' => $request->comment,
                'status' => 'published',
                'images' => !empty($imagePaths) ? json_encode($imagePaths) : null,
            ]);

            // Add full URLs to the response
            $review->images = $imagePaths ? array_map(function ($path) {
                return url($path);
            }, $imagePaths) : [];

            return response()->json([
                'message' => 'Review successfully added',
                'review' => $review,
                'debug_info' => [
                    'image_paths' => $imagePaths,
                    'request_has_files' => $request->hasFile('images'),
                    'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0
                ]
            ], 201);

        } catch (\Exception $e) {
            // Clean up any uploaded images if there's an error
            foreach ($imagePaths as $path) {
                if (File::exists(public_path($path))) {
                    File::delete(public_path($path));
                }
            }
            
            \Log::error('Error while adding review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error occurred: ' . $e->getMessage(),
                'debug_info' => [
                    'error_type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Update a specific review
     */
    public function updateReview(Request $request, $id)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $review = Review::where('id', $id)->where('customer_id', $userId)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found or unauthorized.'], 404);
        }

        $request->validate([
            'star' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $imagePaths = json_decode($review->images, true) ?? [];

            if ($request->hasFile('images')) {
                // Delete old images
                foreach ($imagePaths as $path) {
                    if (File::exists(public_path($path))) {
                        File::delete(public_path($path));
                    }
                }

                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path($this->storagePath), $filename);
                    $imagePaths[] = $this->storagePath . $filename;
                }
            }

            $review->update([
                'star' => $request->input('star', $review->star),
                'comment' => $request->input('comment', $review->comment),
                'images' => !empty($imagePaths) ? json_encode($imagePaths) : null,
            ]);

            // Transform the response to include full image URLs
            $review->images = $imagePaths ? array_map(function ($path) {
                return url($path);
            }, $imagePaths) : [];

            return response()->json(['message' => 'Review updated successfully.', 'review' => $review]);

        } catch (\Exception $e) {
            \Log::error('Error while updating review', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a specific review
     */
    public function deleteReview($id)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $review = Review::where('id', $id)->where('customer_id', $userId)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found or unauthorized.'], 404);
        }

        try {
            // Delete associated images
            $imagePaths = json_decode($review->images, true) ?? [];
            foreach ($imagePaths as $path) {
                if (File::exists(public_path($path))) {
                    File::delete(public_path($path));
                }
            }

            $review->delete();

            return response()->json(['message' => 'Review deleted successfully.']);
            
        } catch (\Exception $e) {
            \Log::error('Error while deleting review', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error occurred: ' . $e->getMessage()], 500);
        }
    }
}