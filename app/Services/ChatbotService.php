<?php

namespace App\Services;

use App\Models\ClinicSetting;
use App\Models\Conversation;
use App\Models\Faq;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Common Indonesian filler words to ignore during normalization.
     *
     * @var array<int, string>
     */
    protected array $stopWords = [
        'halo', 'hai', 'hi', 'hey', 'hei', 'min', 'mimin', 'kak', 'kakak', 'dok', 'dokter',
        'tolong', 'mohon', 'mau', 'tanya', 'bertanya', 'apakah', 'ada', 'apa', 'saja',
        'ya', 'yah', 'dong', 'sih', 'nih', 'deh', 'kan', 'nya', 'kah', 'pun',
        'di', 'ke', 'dari', 'yang', 'ini', 'itu', 'untuk', 'dengan', 'dan', 'atau',
    ];

    /**
     * Process an incoming message and return the bot response array:
     * ['reply' => string, 'faq_id' => ?int, 'action' => 'answered'|'fallback'|'escalated'|'greeting'|'silent']
     *
     * @return array{reply: ?string, faq_id: ?int, action: string}
     */
    public function reply(Conversation $conversation, string $userMessage): array
    {
        $normalized = $this->normalizeText($userMessage);

        // 1. If conversation is escalated to human admin, bot stays silent
        // unless the user sends "RESET" or "BOT" to reactivate the automated bot
        if ($conversation->status === 'escalated') {
            if (in_array($normalized, ['bot', 'reset', 'menu', 'kembali ke bot'], true)) {
                $conversation->update([
                    'status' => 'active',
                    'escalated_at' => null,
                    'escalated_reason' => null,
                ]);

                $welcomeMessage = ClinicSetting::get(
                    'bot_welcome_message',
                    "Bot asisten otomatis telah aktif kembali.\nSilakan tanyakan informasi yang Anda butuhkan."
                );

                return [
                    'reply' => $welcomeMessage,
                    'faq_id' => null,
                    'action' => 'greeting',
                ];
            }

            return [
                'reply' => null,
                'faq_id' => null,
                'action' => 'silent',
            ];
        }

        // 2. Check for human escalation request
        if ($this->isEscalationRequest($normalized)) {
            $conversation->update([
                'status' => 'escalated',
                'escalated_at' => now(),
                'escalated_reason' => 'Pasien meminta bantuan petugas admin langsung.',
            ]);

            $escalateMessage = ClinicSetting::get(
                'bot_escalate_message',
                'Permintaan Anda telah kami teruskan ke petugas administrasi klinik. Mohon ditunggu ya.'
            );

            return [
                'reply' => $escalateMessage,
                'faq_id' => null,
                'action' => 'escalated',
            ];
        }

        // 3. Check for greeting / menu intent
        if ($this->isGreeting($normalized)) {
            $welcomeMessage = ClinicSetting::get(
                'bot_welcome_message',
                "Halo! Selamat datang di layanan informasi klinik kami.\nKetik pertanyaan Anda atau ketik *MENU* untuk bantuan."
            );

            return [
                'reply' => $welcomeMessage,
                'faq_id' => null,
                'action' => 'greeting',
            ];
        }

        // 4. Match against active FAQs
        $matchedFaq = $this->findBestMatchingFaq($userMessage, $normalized);

        if ($matchedFaq !== null) {
            return [
                'reply' => $matchedFaq->answer,
                'faq_id' => $matchedFaq->id,
                'action' => 'answered',
            ];
        }

        // 5. Fallback message if no match found
        $fallbackMessage = ClinicSetting::get(
            'bot_fallback_message',
            'Mohon maaf, kami belum memahami pertanyaan Anda. Silakan ketik *MENU* untuk topik informasi, atau ketik *ADMIN* untuk berbicara dengan petugas.'
        );

        return [
            'reply' => $fallbackMessage,
            'faq_id' => null,
            'action' => 'fallback',
        ];
    }

    /**
     * Find the best matching FAQ based on keyword hits and text similarity.
     */
    public function findBestMatchingFaq(string $rawMessage, string $normalizedMessage): ?Faq
    {
        $faqs = Faq::active()->with('category')->get();
        if ($faqs->isEmpty()) {
            return null;
        }

        $minConfidence = (float) config('whatsapp.min_confidence_score', 0.4);
        $bestScore = 0.0;
        $bestFaq = null;

        $userTokens = explode(' ', $normalizedMessage);

        foreach ($faqs as $faq) {
            $score = 0.0;

            // Check FAQ keywords
            if (is_array($faq->keywords) && ! empty($faq->keywords)) {
                $keywordHits = 0;
                $totalKeywords = count($faq->keywords);

                foreach ($faq->keywords as $keyword) {
                    $normKeyword = $this->normalizeText($keyword);

                    // Check exact phrase match in message
                    if (str_contains($normalizedMessage, $normKeyword)) {
                        $keywordHits += 1.5;

                        continue;
                    }

                    // Check token overlap
                    $kwTokens = explode(' ', $normKeyword);
                    $intersection = array_intersect($kwTokens, $userTokens);
                    if (! empty($intersection)) {
                        $keywordHits += count($intersection) / max(1, count($kwTokens));
                    }
                }

                $keywordScore = min(1.0, $keywordHits / max(1, $totalKeywords));
                $score += $keywordScore * 0.7; // 70% weight on keywords
            }

            // Text similarity with the FAQ question
            $normQuestion = $this->normalizeText($faq->question);
            similar_text($normalizedMessage, $normQuestion, $percentSimilarity);
            $similarityScore = $percentSimilarity / 100.0;
            $score += $similarityScore * 0.3; // 30% weight on full question similarity

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestFaq = $faq;
            }
        }

        return $bestScore >= $minConfidence ? $bestFaq : null;
    }

    /**
     * Normalize text: lowercase, remove punctuation, strip extra whitespace.
     */
    public function normalizeText(string $text): string
    {
        $text = Str::lower($text);
        // Replace non-alphanumeric chars with space
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        // Collapse multiple spaces
        $text = preg_replace('/\s+/', ' ', (string) $text);

        return trim((string) $text);
    }

    /**
     * Check if message is a greeting or menu command.
     */
    protected function isGreeting(string $normalized): bool
    {
        $greetings = [
            'halo', 'hai', 'hi', 'hey', 'hei', 'menu', 'bantuan', 'help', 'info',
            'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam',
            'pagi', 'siang', 'sore', 'malam', 'assalamualaikum', 'assalam',
        ];

        return in_array($normalized, $greetings, true);
    }

    /**
     * Check if message asks for a human admin.
     */
    protected function isEscalationRequest(string $normalized): bool
    {
        $escalationKeywords = [
            'admin', 'operator', 'cs', 'customer service', 'manusia',
            'petugas', 'hubungi staf', 'bicara orang', 'bantuan orang',
        ];

        foreach ($escalationKeywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
