<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ReplaceTicketMessagesFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $ticketMessages = (new \App\Services\TicketMessageService())->get();
        foreach ($ticketMessages as $message) {
            if ($message->file &&
                Storage::exists('/public/images/ticket-messages/'.$message->file) &&
                !Storage::exists('/images/ticket-messages/'.$message->file)) {
                    Storage::copy('public/images/ticket-messages/'.$message->file, 'images/ticket-messages/'.$message->file);
                    Storage::delete('public/images/ticket-messages/'.$message->file);
            }
        }
        if (Storage::exists('public/images/blocked-wallets')) {
            Storage::move('public/images/blocked-wallets', 'images/blocked-wallets');
        }
        if (Storage::exists('public/images/unblocked-wallets')) {
            Storage::move('public/images/unblocked-wallets', 'images/unblocked-wallets');
        }
        if (Storage::exists('public/images')) {
            Storage::deleteDirectory('public/images');
        }
        if (Storage::exists('public/pdf')) {
            Storage::deleteDirectory('public/pdf');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
