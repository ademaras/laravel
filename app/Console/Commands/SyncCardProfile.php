<?php

namespace App\Console\Commands;

use App\Models\Cards;
use App\ModelsOld\Cards as OldCards;
use App\Models\User;
use Illuminate\Console\Command;

class SyncCardProfile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:card-profile {all? : all records or last 100 records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync card profile';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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

                OldCards::whereNotNull('user_id')
                    ->orderBy('id', 'asc') // id'ye göre artan sırayla al
                    ->chunkById(1000, function ($cards) {
                        $this->saveProfile($cards);
                    });
                break;

            case 'last':
                $this->info('Last 100 records is importing...');

                OldCards::whereNotNull('user_id')
                    ->orderBy('id', 'desc') // id'ye göre azalan sırayla son kayıtlardan başla
                    ->limit(1000) // sadece son 1000 kaydı al
                    ->chunk(100, function ($cards) {
                        $this->saveProfile($cards);
                    });
                break;

            default:
                $this->error('Invalid option.');
                return Command::FAILURE;
        }

        $this->info('Card profiles synced successfully');

        return Command::SUCCESS;
    }

    private function saveProfile($cards)
    {
        foreach ($cards as $card) {
            $result = $card->toArray();

            $this->line('CardID: ' . $result['id'] . " -- UserID: " . $result['user_id']);

            $user = User::find($result['user_id']);

            if (empty($user)) {
                continue;
            }

            if ($user->personalCard()->exists() == false) {
                $user->personalCard()->create([
                    'user_id' => $user->id,
                ]);
            }

            if ($user->userBasic()->exists() == false) {
                $user->userBasic()->create([
                    'card_id' => $user->personalCard->id,
                    'name' => $user->name,
                ]);
            }

            try {
                $user->userBasic->title = $result['slug'];
                $user->userBasic->name = $result['name'];

                if (strlen($result['biography']) >= 254) {
                    $user->userBasic->about = $result['biography'];    
                } else {
                    $user->userBasic->job = $result['biography'];
                }
                
                $user->userBasic->profile_img = 'storage/panel/assets/files/users/avatar/' . $result['image'];
                $user->userBasic->bg_img = 'storage/panel/assets/files/users/avatar/' . $result['cover_image'];
                $user->userBasic->save();

                if ($user->userCards()->exists()) {
                    $user->personalCard->card_id = $user->userCards()->first()->id;
                    $user->personalCard->save();
                }

                $user->parent_id = $result['parent_id'];
                $user->user_type = 3;
                $user->country_code = 90;
                $user->phone = $result['phone'];
                $user->username = $result['slug'];
                $user->email = $result['mail'];
                $user->save();
            } catch (\Exception $e) {
                $this->error("Failed to save profile card with ID {$result['id']}: " . $e->getMessage());
            }
        }
    }
}
