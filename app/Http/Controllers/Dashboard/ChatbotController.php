<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChatbotEntry;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function knowledge(Request $request): View
    {
        $products = $this->loadProducts();
        $selectedProductId = (int) $request->query('product', 0);

        if ($selectedProductId === 0 && $products->isNotEmpty()) {
            $selectedProductId = (int) $products->first()->id;
        }

        return view('chatbot.knowledge', [
            'products' => $products,
            'selectedProductId' => $selectedProductId,
        ]);
    }

    public function existing(Request $request): View
    {
        $products = $this->loadProducts();
        $selectedProductId = (int) $request->query('product', 0);

        if ($selectedProductId === 0 && $products->isNotEmpty()) {
            $selectedProductId = (int) $products->first()->id;
        }

        $knowledgeEntries = collect();
        if ($selectedProductId > 0) {
            $knowledgeEntries = ChatbotEntry::query()
                ->where('product_id', $selectedProductId)
                ->orderBy('question')
                ->get(['id', 'question', 'answer', 'created_at']);
        }

        return view('chatbot.existing', [
            'products' => $products,
            'selectedProductId' => $selectedProductId,
            'knowledgeEntries' => $knowledgeEntries,
        ]);
    }

    public function simulator(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            $data = $request->validate([
                'product' => ['required', 'integer', 'exists:products,id'],
                'term' => ['required', 'string'],
            ]);

            $productId = (int) $data['product'];
            $searchTerm = trim($data['term']);

            $query = ChatbotEntry::query()
                ->where('product_id', $productId)
                ->orderBy('question');

            if ($searchTerm !== '' && strcasecmp($searchTerm, 'all') !== 0) {
                $query->search($searchTerm);
            }

            $results = $query->get(['question', 'answer']);

            return response()->json([
                'product' => Product::query()->find($productId, ['id', 'name']),
                'query' => $searchTerm,
                'answers' => $results->map(fn (ChatbotEntry $entry) => [
                    'question' => $entry->question,
                    'answer' => $entry->answer,
                ])->values(),
                'timestamp' => now()->timezone('Asia/Kathmandu')->toIso8601String(),
            ]);
        }

        $products = $this->loadProducts();
        $selectedProductId = (int) $request->query('product', 0);
        $searchTerm = trim((string) $request->query('term', ''));
        $chatbotResults = collect();

        if ($selectedProductId > 0) {
            $query = ChatbotEntry::query()
                ->where('product_id', $selectedProductId)
                ->orderBy('question');

            if ($searchTerm !== '' && strcasecmp($searchTerm, 'all') !== 0) {
                $query->search($searchTerm);
            }

            if ($searchTerm !== '') {
                $chatbotResults = $query->get(['question', 'answer']);
            }
        }

        return view('chatbot.simulator', [
            'products' => $products,
            'selectedProductId' => $selectedProductId,
            'searchTerm' => $searchTerm,
            'chatbotResults' => $chatbotResults,
        ]);
    }

    public function edit(Request $request, ChatbotEntry $chatbotEntry): View
    {
        $products = $this->loadProducts();

        return view('chatbot.edit', [
            'entry' => $chatbotEntry,
            'products' => $products,
            'redirectProduct' => (int) $request->query('product', $chatbotEntry->product_id),
        ]);
    }

    public function update(Request $request, ChatbotEntry $chatbotEntry): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'redirect_product' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $chatbotEntry->update([
            'question' => trim($data['question']),
            'answer' => trim($data['answer']),
        ]);

        $productId = (int) ($data['redirect_product'] ?? $chatbotEntry->product_id);

        return Redirect::route('chatbot.existing', ['product' => $productId])
            ->with('status', 'Knowledge entry updated.');
    }

    public function destroy(Request $request, ChatbotEntry $chatbotEntry): RedirectResponse
    {
        $productId = (int) $request->input('redirect_product', $chatbotEntry->product_id);
        $chatbotEntry->delete();

        return Redirect::route('chatbot.existing', ['product' => $productId])
            ->with('status', 'Knowledge entry removed.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'questions' => ['required', 'array'],
            'questions.*' => ['nullable', 'string'],
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'string'],
        ]);

        $paired = collect($data['questions'])
            ->map(fn ($question, $index) => [
                'question' => trim((string) $question),
                'answer' => trim((string) ($data['answers'][$index] ?? '')),
            ])
            ->filter(fn ($pair) => $pair['question'] !== '' && $pair['answer'] !== '');

        if ($paired->isEmpty()) {
            throw ValidationException::withMessages([
                'questions' => 'Provide at least one question and answer pair.',
            ]);
        }

        $now = now();
        $records = $paired->map(fn ($pair) => [
            'product_id' => (int) $data['product_id'],
            'question' => $pair['question'],
            'answer' => $pair['answer'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        ChatbotEntry::query()->insert($records->all());

        return redirect()
            ->route('chatbot.knowledge', ['product' => $data['product_id']])
            ->with('status', 'Chatbot knowledge saved successfully.');
    }

    private function loadProducts()
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
