<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function create()
    {
        return Inertia::render('Category/AddCategory');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name'
        ]);

        Category::create($validated);

        return redirect()->route('home')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function index()
    {
        $categories = Category::withCount('products')->get();

        return response()->json($categories);
    }
}
