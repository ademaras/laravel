<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Choice;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function index($id)
    {
        $survey = Survey::find($id);
        return view('user.question.index', compact('survey'));
    }

    public function create($survey_id)
    {
        $survey = Survey::find($survey_id);
        return view('user.question.create', compact('survey'));
    }

    public function store(Request $request, $survey_id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'list_no' => 'required',
        ], [
            'list_no.required' => 'List no boş bırakılamaz.',
            'title.required' => 'Soru başlığı boş bırakılamaz.',
            'title.max' => 'Soru başlığı en fazla :max karakter olmalıdır.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $survey = Survey::findOrFail($survey_id);

        $question = new Question();
        $question->list_no = $request->input('list_no');
        $question->title = $request->input('title');
        $survey->questions()->save($question);

        $choices = $request->input('choices', []);

        foreach ($choices as $choiceText) {
            if (!empty($choiceText)) {
                $choice = new Choice();
                $choice->choice_text = $choiceText;
                $question->choices()->save($choice);
            }
        }

        if ($question) {
            return redirect()->route('user.question.index', $survey_id)->with('success', 'Soru başarıyla oluşturuldu.');
        } else {
            return back()->with('error', 'Soru oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function answer($question_id)
    {
        $answers = Answer::where('question_id', $question_id)->get();
        return view('user.answer.index', compact('answers'));
    }

    public function edit($id)
    {
        $question = Question::find($id);
        return view('user.question.edit', compact('question'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'list_no' => 'required',
        ], [
            'list_no.required' => 'List no boş bırakılamaz.',
            'title.required' => 'Soru başlığı boş bırakılamaz.',
            'title.max' => 'Soru başlığı en fazla :max karakter olmalıdır.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $question = Question::findOrFail($id);

        $question->list_no = $request->input('list_no');
        $question->title = $request->input('title');
        $question->save();

        $choices = $request->input('choices', []);

        foreach ($question->choices as $index => $choice) {
            if (isset($choices[$index])) {
                $choice->choice_text = $choices[$index];
                $choice->save();
            }
        }

        for ($i = count($question->choices); $i < count($choices); $i++) {
            $newChoice = new Choice();
            $newChoice->choice_text = $choices[$i];
            $question->choices()->save($newChoice);
        }

        if ($question) {
            return redirect()->back()->with('success', 'Soru başarıyla güncellendi.');
        } else {
            return back()->with('error', 'Soru güncellendi bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function destroy($id)
    {
        $question = Question::find($id);
        if ($question) {
            $question->delete();
        }
        return back()->with('success', 'Soru başarıyla silindi.');
    }
}
