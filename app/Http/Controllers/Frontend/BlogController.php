<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogDetails;
use App\Models\Content;
use App\Models\Page;
use App\Models\PageDetail;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function blog()
    {
        $data['pageSeo'] = Page::where('name', 'blog')->select('page_title')->first();
        $data['content'] = Content::with('contentDetails')->where('name','blog')
            ->where('type', 'single')
            ->first();
        $data['blogs'] = Blog::query()
            ->where('status',1)
            ->with('category', 'details')
            ->orderBy('id', 'desc')
            ->paginate(6);
        return view(template().'blogs', $data);
    }

    public function blogDetails($slug)
    {
        $data['latestBlogs'] = Blog::query()
            ->where('status', '1')
            ->with('details')
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get();

        $data['categories'] = BlogCategory::query()->latest()->get();

        $data['blogDetails'] = BlogDetails::with('blog')->where('slug', $slug)->first();
        return view(template().'blog_details', $data);
    }

    public function blogSearch(Request $request)
    {
        $search = $request->search;
        $data['blogs'] = Blog::with('details', 'category')->where('status', 1)
            ->where(function ($query) use ($search) {
                $query->whereHas('category', fn ($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhereHas('details', fn ($q) => $q->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%"));
            })
            ->latest()->paginate(3);
        return view(template() . 'blogs', $data);
    }

    public function blogByCategory($slug = 'category-title', $id){
        $data['blogs'] = Blog::with(['details', 'category'])
            ->where('category_id', $id)
            ->where('status', 1)
            ->latest()
            ->paginate(3);
        return view(template() . 'blogs', $data);
    }

}
