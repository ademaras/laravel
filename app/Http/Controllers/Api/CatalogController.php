<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Catalog;
use App\Models\UserLinkInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CatalogController extends Controller
{
    public function add(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:0,1,2',
            'link' => 'nullable',
            'file' => 'nullable',
            'description' => 'nullable',
            'title' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $getCatalogs = Catalog::where('user_id', $user->id)->get();
        foreach ($getCatalogs as $getCatalog) {
            $getCatalog->order += 1;
            $getCatalog->save();
        }

        $userLinkOrders = UserLinkInfo::where('user_id', $user->id)->get();
        foreach ($userLinkOrders as $userLinkOrder) {
            $userLinkOrder->order += 1;
            $userLinkOrder->save();
        }

        $catalog = new Catalog();
        $catalog->user_id = $user->id;
        $catalog->title = $request->title;
        $catalog->type = $request->type;
        $catalog->link = $request->link;
        $catalog->is_business = $request->is_business;
        $catalog->description = $request->description;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/catalogs'), $file_name);
            $catalog->file = $file_name;
        }

        $catalog->save();

        return $this->mobile(true, 'Katalog başarıyla eklendi');
    }

    public function index()
    {
        $user = Auth::user();
        $catalogs = Catalog::where('user_id', $user->id)->get();

        return $this->mobile(true, 'Kataloglar', $catalogs);
    }
}
