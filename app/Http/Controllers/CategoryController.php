<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function showBarcode($id)
    {
        $product = Product::findOrFail($id);

        return view('barcode', compact('product'));
    }
    public function index()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        Gate::allows('hasRole', ['Admin', 'Manager']);
        // $paginatedcategories = Category::with('parent')->latest()->paginate(10);
        // $allcategories = Category::with('parent')->latest()->get();
        // $allcategories = Category::with('parent')->latest()->get()
        $allcategories = Category::with('parent')
            ->orderBy('created_at', 'desc')
            ->get() // Get the collection
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                    ] : null,
                    'hierarchy_string' => $category->hierarchy_string, // Add this
                ];
            });


        return Inertia::render('Categories/Index', [
            // 'paginatedcategories' => $paginatedcategories,
            'allcategories' => $allcategories,
            'totalCategories' => $allcategories->count()
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Categories/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->banner('Category created successfully.');

        // return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        // Check for circular relationship
        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);

            // Traverse up the hierarchy to check for circular references
            while ($parent) {
                if ($parent->id === $category->id) {
                    return back()->withErrors(['parent_id' => 'A category cannot be its own parent or ancestor.']);
                }
                $parent = $parent->parent; // Assuming a `parent` relationship exists in your Category model
            }
        }

        $category->update($validated);

        return redirect()->route('categories.index')->banner('Category updated successfully.');

        // return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        $category->delete();
        return redirect()->route('categories.index')->banner('Category Deleted successfully.');
    }

    public function topCategories(Request $request)
    {
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get() // Get the collection
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'hierarchy_string' => $category->hierarchy_string, // Include hierarchy_string for parent
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'hierarchy_string' => $child->hierarchy_string, // Include hierarchy_string for children
                        ];
                    }),
                ];
            });

        // Return the categories as a JSON response
        return response()->json([
            'categories' => $categories,
        ]);
    }
}
