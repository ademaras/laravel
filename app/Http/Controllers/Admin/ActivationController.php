<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActivationCodeExport;
use App\Models\Cards;
use App\Models\DijiCard;
use App\Models\DijicardCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ActivationController extends Controller
{
    public function index()
    {
        $activations = Cards::orderBy('id', 'desc')->paginate(10);

        return view('admin.activation.index', compact('activations'));
    }

    public function search(Request $request)
    {
        // Arama sorgusu
        $search = $request->input('search');

        $activations = Cards::query()
            ->when($search, function ($query) use ($search) {
                $query->where('activation_code', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc') // Son eklenen kayıtları önce göster
            ->paginate(10); // 10'lu sayfalama

        return view('admin.activation.index', compact('activations', 'search'));
    }

    public function create()
    {
        $categories = DijicardCategory::get();
        return view('admin.activation.create',compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'required',
                'count' => 'required',
            ],
            [
                'type.required' =>'Türü alanı boş bırakılamaz',
                'count.required' =>'Aktivasyon kod adeti alanı boş bırakılamaz',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $codeCount = $request->count;
        $createdActivationCodes = [];

        for ($i = 1; $i <= $codeCount; $i++) {
            $createdKey = md5(microtime() . rand());
            $data = Cards::where('activation_code', $createdKey)->first();
            
            // Eski olusturulan aktivasyon kodlari ile cakismamasi icin
            // eski kodlar bu tabloya tasindi ve bu kontrol eklendi.
            $oldData = DijiCard::where('code', $createdKey)->first();

            if (!$data && !$oldData) {
                $createdActivationCodes[] = [$i, substr($createdKey, 0, 13)];
            } else {
                $i--;
            }
        }

        foreach ($createdActivationCodes as $activationCode) {
            $card = new Cards();
            $card->type = $request->type;
            $card->activation_code = $activationCode[1];
            $card->used = 0;
            $card->save();
        }

        // TODO ureten admin yoneticisini loglayacak
        DB::connection('oldmysql')->table('generated_activation_log')->insert([
            'card_id' => Auth::user()->id,
            'created_name' => Auth::user()->name,
            'created_count' => $codeCount,
            'created_first' => $createdActivationCodes[0][1]
        ]);

        // return redirect()->route('admin.activationCode.index');
        return Excel::download(new ActivationCodeExport($createdActivationCodes), 'activation_code_' . date('d-m-Y-H-i') . '.xlsx');
    }

    public function destroy($id)
    {
        $card = Cards::find($id);

        if (!$card) {
            return redirect()->back()->withErrors("Bulunamadı")->withInput();
        }

        $card->delete();
        
        return redirect()->back()->with("Başarıyla silindi");
    }
}
