<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\LinksImport;
use Maatwebsite\Excel\Facades\Excel;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $file = $request->file('excel_file');
            
            Excel::import(new LinksImport, $file);


            return response()->json(['message' => 'Excel dosyası başarıyla yüklendi ve veriler işlendi.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Excel dosyası yüklenirken bir hata oluştu: ' . $e->getMessage()], 500);
        }
    }

    public function export()
    {
        // return Excel::download(new LinksImport, 'users.xlsx');
    }
}
