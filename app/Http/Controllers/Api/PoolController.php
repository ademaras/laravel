<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pool;
use App\Models\PoolAnswer;
use App\Models\PoolUserAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PoolController extends Controller
{
    public function list()
    {
        $pool = Pool::where('business_id', Auth::user()->id)->with('answers')->get();
        $data = [];

        foreach ($pool as $key => $value) {
            $data[$key] = $value;

            $data[$key]['totalAnswered'] = $value->answers->sum(function ($item) {
                return $item->answeredUsers->count();
            });

            foreach ($value->answers as $k => $v) {
                $data[$key]['answers'][$k]['id'] = $v->id;
                $data[$key]['answers'][$k]['answer'] = $v->answer;
                $data[$key]['answers'][$k]['totalAnswered'] = $v->answeredUsers->count();
            }
        }


        return $this->mobile(true, 'Pool', $pool);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:pools,name',
            'question' => 'required|string|max:255',
            'answers' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $pool = Pool::create([
            'business_id' => Auth::id(),
            'name' => $request->name,
            'question' => $request->question,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        foreach ($request->answers as $answer) {
            PoolAnswer::create([
                'pool_id' => $pool->id,
                'answer' => $answer,
            ]);
        }

        return $this->mobile(true, 'Pool created successfully');
    }

    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'question' => 'required|string|max:255',
            'answers' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $pool = Pool::find($id);
        $pool->name = $request->name;
        $pool->question = $request->question;
        $pool->start_date = $request->start_date;
        $pool->end_date = $request->end_date;
        $pool->save();

        $pool->answers()->delete();
        foreach ($request->answers as $answer) {
            PoolAnswer::create([
                'pool_id' => $pool->id,
                'answer' => $answer,
            ]);
        }

        return $this->mobile(true, 'Pool updated successfully');
    }

    public function delete($id)
    {
        $pool = Pool::find($id);

        if (!$pool) {
            return $this->mobile(false, 'Pool not found');
        }

        $pool->answers()->delete();
        $pool->delete();

        return $this->mobile(true, 'Pool deleted successfully');
    }


    public function answer(Request $request, $id)
    {
        $user_answer = PoolUserAnswer::create([
            'user_id' => Auth::user()->id,
            'answer_id' => $request->answer_id,
            'pool_id' => $id,
        ]);

        return $this->mobile(true, 'Answered successfully', $user_answer);
    }
}
