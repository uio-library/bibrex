<?php

namespace App\Events;

use App\Loan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LoanTableUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Action to highlight
     *
     * @var string
     */
    public $action;

    /**
     * Loan to highlight
     *
     * @var string
     */
    public $loan;

    /**
     * Sender window
     *
     * @var string
     */
    public $sender;

    /**
     * Create a new event instance.
     *
     * @param string $action
     * @param Request $request
     * @param Loan|null $loan
     */
    public function __construct(string $action, Request $request, Loan $loan = null)
    {
        $this->action = $action;
        $this->sender = $request->headers->get('x-bibrex-window');
        $this->loan = $loan ? $loan->toArray() : null;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $library = \Auth::user();
        return new PrivateChannel('loans.' . $library->id);
    }
}
