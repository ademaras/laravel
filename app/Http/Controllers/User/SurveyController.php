<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::get();
        return view('user.survey.index', compact('surveys'));
    }

    public function create()
    {
        return view('user.survey.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ], [
            'title.required' => 'Anket başlığı boş bırakılamaz.',
            'title.max' => 'Anket başlığı en fazla :max karakter olmalıdır.',
            'start_date.required' => 'Başlangıç tarihi boş bırakılamaz.',
            'start_date.date' => 'Başlangıç tarihi geçerli bir tarih formatına sahip olmalıdır.',
            'end_date.required' => 'Bitiş tarihi boş bırakılamaz.',
            'end_date.date' => 'Bitiş tarihi geçerli bir tarih formatına sahip olmalıdır.',
            'end_date.after' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $survey = new Survey();
        $survey->title = $request->title;
        $survey->start_date = $request->start_date;
        $survey->end_date = $request->end_date;
        $survey->save();

        if ($survey) {
            return redirect()->route('user.survey.index')->with('success', 'Anket başarıyla oluşturuldu.');
        } else {
            return back()->with('error', 'Anket oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function show($id)
    {
        $survey = Survey::find($id);
        return view('user.survey.show', compact('survey'));
    }

    public function edit($id)
    {
        $survey = Survey::find($id);
        return view('user.survey.edit', compact('survey'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ], [
            'title.required' => 'Anket başlığı boş bırakılamaz.',
            'title.max' => 'Anket başlığı en fazla :max karakter olmalıdır.',
            'start_date.required' => 'Başlangıç tarihi boş bırakılamaz.',
            'start_date.date' => 'Başlangıç tarihi geçerli bir tarih formatına sahip olmalıdır.',
            'end_date.required' => 'Bitiş tarihi boş bırakılamaz.',
            'end_date.date' => 'Bitiş tarihi geçerli bir tarih formatına sahip olmalıdır.',
            'end_date.after' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $survey = Survey::find($id);

        if (!$survey) {
            return back()->with('error', 'Anket bulunamadı.');
        }

        $survey->title = $request->title;
        $survey->start_date = $request->start_date;
        $survey->end_date = $request->end_date;
        $survey->save();

        if ($survey) {
            return redirect()->route('user.survey.index')->with('success', 'Anket başarıyla güncellendi.');
        } else {
            return back()->with('error', 'Anket güncellenirken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function destroy($id)
    {
        $survey = Survey::find($id);
        if ($survey) {
            $survey->delete();
            return back()->with('success', 'Anket başarıyla silindi.');
        }
        else
        {
            return back()->with('error', 'Anket silinemedi.');
        }
    }
}
