<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Links;
use Illuminate\Http\Request;
use App\Models\LinkCategories;
use Illuminate\Support\Facades\Validator;

class LinkController extends Controller
{
    public function categories()
    {
        $categories = LinkCategories::all();
        return view('admin.links.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function category_create()
    {
        return view('admin.links.categories.create');
    }

    public function category_create_post(Request $request)
    {
        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'icon' => 'required|image',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $categories = new LinkCategories();
            $categories->name = $request->input('title');

            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/linkCategory', $filename);
                $categories->icon = 'storage/linkCategory/' . $filename;
            }
            $categories->save();
            return redirect()->route('admin.links.index')->with('success', 'Kategori oluşturma işlemi başarılı.');
        }
    }

    public function category_edit($id)
    {
        $categories = LinkCategories::find($id);
        return view('admin.links.categories.edit', [
            'categories' => $categories,
        ]);
    }

    public function category_edit_post(Request $request)
    {
        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'icon' => 'nullable|image',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $categories = LinkCategories::find($request->input('category_id'));
            if ($categories != null) {
                $categories->name = $request->input('title');

                if ($request->hasFile('icon')) {
                    $file = $request->file('icon');
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/linkCategory', $filename);
                    $categories->icon = 'storage/linkCategory/' . $filename;
                }
                $categories->save();
                return redirect()->route('admin.links.index')->with('success', 'Kategori güncelleme işlemi başarılı.');
            } else {
                return redirect()->back()->with('errors', 'Kategori bulunamadı!');
            }
        }
    }

    public function categoryDelete($id)
    {
        $categories = LinkCategories::find($id);

        if (!$categories) {
            return redirect()->back()->with('Kategori bulunamadı!');
        } else {
            $categories->delete();
            return redirect()->back()->with('Kategori başarıyla silindi.');
        }
    }

    public function sub_categories()
    {
        $links = Links::with('category')->get();
        return view('admin.links.sub-categories.index', [
            'links' => $links,
        ]);
    }

    public function sub_category_create()
    {
        $category = LinkCategories::all();
        return view('admin.links.sub-categories.create', [
            'category' => $category,
        ]);
    }

    public function sub_category_create_post(Request $request)
    {
        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category' => 'required',
            'icon' => 'required|image',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $link = new Links();
            $link->name = $request->input('title');
            $link->link_category_id = $request->input('category');

            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/icons', $filename);
                $link->icon = 'storage/icons/' . $filename;
            }
            $link->save();
            return back();
            // return redirect()->route('admin.links.sub.index')->with('success', 'Bağlantı oluşturma işlemi başarılı.');
        }
    }

    public function sub_category_edit($id)
    {
        $link = Links::find($id);
        $category = LinkCategories::all();

        return view('admin.links.sub-categories.edit', [
            'category' => $category,
            'link' => $link,
        ]);
    }

    public function sub_category_edit_post(Request $request)
    {
        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'icon' => 'nullable|image',
            'category' => 'required',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $link = Links::find($request->input('link_id'));
            if ($link != null) {
                $link->name = $request->input('title');
                $link->link_category_id = $request->input('category');

                if ($request->hasFile('icon')) {
                    $file = $request->file('icon');
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/icons', $filename);
                    $link->icon = 'storage/icons/' . $filename;
                }
                $link->save();
                return redirect()->route('admin.links.sub.index')->with('success', 'Bağlantı güncelleme işlemi başarılı.');
            } else {
                return redirect()->back()->with('errors', 'Bağlantı bulunamadı!');
            }
        }
    }

    public function sub_categoryDelete($id)
    {
        $link = Links::find($id);

        if (!$link) {
            return redirect()->back()->with('Bağlantı bulunamadı!');
        } else {
            $link->delete();
            return redirect()->back()->with('Bağlantı başarıyla silindi.');
        }
    }
}
