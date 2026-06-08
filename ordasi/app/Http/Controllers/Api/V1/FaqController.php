<?php

namespace App\Http\Controllers\Api\V1;

use App\Faq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:settings.edit')->only(['store', 'update', 'destroy']);
        // index es público
    }

    public function index(Request $request)
    {
        $query = Faq::orderBy('order_column')->orderBy('id');
        if ($s = $request->query('search')) {
            $query->where(fn ($q) => $q->where('question', 'like', "%{$s}%")->orWhere('answer', 'like', "%{$s}%"));
        }
        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        return response()->json(['data' => Faq::create($this->validateData($request))], 201);
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validateData($request));
        return response()->json(['data' => $faq]);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json(null, 204);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category'     => ['nullable', 'string', 'max:120'],
            'question'     => ['required', 'string', 'max:255'],
            'answer'       => ['required', 'string'],
            'order_column' => ['nullable', 'integer'],
        ]);
    }
}
