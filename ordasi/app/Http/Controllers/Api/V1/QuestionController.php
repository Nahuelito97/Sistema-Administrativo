<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ScopesToSeller;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Product;
use App\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use ScopesToSeller;

    public function __construct()
    {
        $this->middleware('can:questions.index')->only(['sellerIndex']);
        $this->middleware('can:questions.answer')->only(['answer']);
        // publicIndex: público | store: cualquier usuario autenticado
    }

    /** Preguntas públicas de un producto (las respondidas + las recientes). */
    public function publicIndex(Product $product)
    {
        $questions = $product->questions()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return QuestionResource::collection($questions);
    }

    /** Un usuario autenticado hace una pregunta sobre un producto. */
    public function store(Request $request, Product $product)
    {
        $data = $request->validate(['question' => ['required', 'string', 'max:500']]);

        $question = $product->questions()->create([
            'user_id'  => $request->user()->id,
            'question' => $data['question'],
        ]);

        return (new QuestionResource($question->load('user')))->response()->setStatusCode(201);
    }

    // ---------- Vendedor (scoped) ----------

    /** Preguntas a los productos de mi tienda. */
    public function sellerIndex(Request $request)
    {
        $query = Question::with(['product', 'user'])
            ->whereHas('product', fn ($q) => $this->scopeOwned($q, $request));

        if ($request->query('status') === 'pending') {
            $query->whereNull('answered_at');
        } elseif ($request->query('status') === 'answered') {
            $query->whereNotNull('answered_at');
        }

        return QuestionResource::collection($query->latest()->paginate(20));
    }

    /** El vendedor responde una pregunta de un producto de su tienda. */
    public function answer(Request $request, Question $question)
    {
        $this->assertOwned($question->product, $request);
        $data = $request->validate(['answer' => ['required', 'string', 'max:1000']]);

        $question->update(['answer' => $data['answer'], 'answered_at' => now()]);

        return new QuestionResource($question->load(['product', 'user']));
    }
}
