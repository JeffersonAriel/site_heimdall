<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Orders\Models\Coupon;
use Modules\Orders\Models\Wishlist;
use Modules\Orders\Models\ProductReview;
use Modules\Orders\Models\BlogPost;
use Illuminate\Support\Facades\Auth;

class EcommerceController extends Controller
{
    // --- COUPON ENDPOINTS ---
    public function listCoupons()
    {
        return response()->json(Coupon::all());
    }

    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_order_value' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
        ]);

        $coupon = Coupon::create($request->all());
        return response()->json($coupon, 201);
    }

    // --- WISHLIST ENDPOINTS ---
    public function wishlist()
    {
        $customerId = Auth::guard('customer')->id() ?? 1; // fallback test
        $wishlist = Wishlist::with('product')->where('customer_id', $customerId)->get();
        return response()->json($wishlist);
    }

    public function addToWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $customerId = Auth::guard('customer')->id() ?? 1;

        $wish = Wishlist::firstOrCreate([
            'customer_id' => $customerId,
            'product_id' => $request->product_id,
        ]);

        return response()->json($wish, 201);
    }

    public function removeFromWishlist($productId)
    {
        $customerId = Auth::guard('customer')->id() ?? 1;
        Wishlist::where('customer_id', $customerId)->where('product_id', $productId)->delete();
        return response()->json(['message' => 'Removido da lista de desejos.']);
    }

    // --- PRODUCT REVIEWS ---
    public function reviews($productId)
    {
        $reviews = ProductReview::with('customer')
            ->where('product_id', $productId)
            ->where('approved', true)
            ->get();
        return response()->json($reviews);
    }

    public function storeReview(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $customerId = Auth::guard('customer')->id() ?? 1;

        $review = ProductReview::create([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => true, // Auto approved for simplicity
        ]);

        return response()->json($review, 201);
    }

    // --- BLOG POSTS ---
    public function blog()
    {
        return response()->json(BlogPost::orderBy('created_at', 'desc')->get());
    }

    public function blogPost($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        return response()->json($post);
    }

    public function storeBlogPost(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->title);
        
        $post = BlogPost::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'published_at' => now(),
        ]);

        return response()->json($post, 201);
    }
}
