<?php

namespace App\Listeners;

use App\Events\CommandeCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignLivraisonToCommande
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CommandeCreated  $event
     * @return void
     */
    public function handle(CommandeCreated $event)
    {
        //
    }
}
