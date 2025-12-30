<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->limit ?? 8;

        $products = Product::with(['brand:id,name', 'category:id,name'])
            ->paginate($limit);

        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $images = [];
            foreach ($request->file('image') as $file) {
                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('productImages'), $name);
                $images[] = 'productImages/' . $name;
            }
            $data['image'] = $images;
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Thêm product thành công',
            'data' => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with(['brand:id,name', 'category:id,name'])
            ->findOrFail($id);

        return response()->json($product);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        $oldImages = $product->image ?? [];
        if ($request->imageDelete) {
            $deleteList = json_decode($request->imageDelete, true);
            foreach ($deleteList as $img) {
                File::delete(public_path($img));
            }
            $oldImages = array_diff($oldImages, $deleteList);
        }

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('productImages'), $name);
                $oldImages[] = 'productImages/' . $name;
            }
        }
        $data['image'] = array_values($oldImages);
        $product->update($data);

        return response()->json(['message' => 'Update product thành công']);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        return response()->json(['message' => 'Xóa product thành công']);
    }

    public function trash()
    {
        $products = Product::onlyTrashed()
            ->with(['brand:id,name', 'category:id,name'])
            ->get();

        return response()->json($products);
    }

    public function restore($id)
    {
        Product::onlyTrashed()->where('id', $id)->restore();

        return response()->json(['message' => 'Khôi phục sản phẩm thành công']);
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        if ($product->image) {
            foreach ($product->image as $img) {
                File::delete(public_path($img));
            }
        }

        $product->forceDelete();

        return response()->json(['message' => 'Xóa vĩnh viễn sản phẩm thành công']);
    }
}
