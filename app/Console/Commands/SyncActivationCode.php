<?php

namespace App\Console\Commands;

use App\Models\Cards;
use App\ModelsOld\Cards as OldCards;
use Illuminate\Console\Command;

class SyncActivationCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:activation-code {all? : all records or last 100 records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync activation code';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Asagidaki kodlar ile profiller aciliyor, bunlari sekronize etmemiz gerekiyor
        // card_codes.code = '$current_url'
        // or cards.code =  '$current_url' 
        // or slug = '$current_url' 
        // or second_code = '$current_url';"

        // Argümanı al (null olabilir)
        $option = $this->argument('all');

        // Eğer argüman verilmemişse, choice metodunu kullan
        if (empty($option)) {
            // Seçenekleri ayarlayın
            $option = $this->choice(
                'Import all records or last 100 records', // Sorulacak soru
                ['all', 'last'], // Seçenekler
                1 // Varsayılan seçenek (ilk seçenek)
            );
        }

        // Seçime göre işlem yap
        switch ($option) {
            case 'all':
                $this->info('All records is importing...');

                OldCards::whereNotNull('code') // 'status' sütunu null olmayanları seç
                    ->whereNotNull('user_id')
                    ->orderBy('id', 'asc') // id'ye göre artan sırayla al
                    ->chunkById(1000, function ($cards) {
                        $this->saveActivationCode($cards);
                    });
                break;

            case 'last':
                $this->info('Last 100 records is importing...');

                OldCards::whereNotNull('code') // 'status' sütunu null olmayanları seç
                    ->whereNotNull('user_id')
                    ->orderBy('id', 'desc') // id'ye göre azalan sırayla son kayıtlardan başla
                    ->limit(1000) // sadece son 1000 kaydı al
                    ->chunk(100, function ($cards) {
                        $this->saveActivationCode($cards);
                    });
                break;

            default:
                $this->error('Invalid option.');
                return Command::FAILURE;
        }

        $this->info('Cards synced successfully');

        return Command::SUCCESS;
    }

    private function saveActivationCode($cards)
    {
        foreach ($cards as $card) {
            $result = $card->toArray();

            $this->line($result['id'] . " -- " . $result['user_id'] . " -- " . $result['code']);

            if (empty($result['code'])) {
                continue;
            }

            $code = Cards::find($result['id']);

            if (empty($code)) {
                $code = new Cards();
                $code->id = $result['id'];
            }

            try {
                $code->user_id = $result['user_id'];
                $code->type = 2;
                $code->activation_code = $result['code'];
                $code->used = 1;
                $code->updated_at = now();
                $code->save();
            } catch (\Exception $e) {
                $this->error("Failed to save card with ID {$result['id']}: " . $e->getMessage());
            }
        }
    }
}
