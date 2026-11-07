<?php

namespace App\Jobs;

use App\Models\SupportTicket;
use App\Traits\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UserAllRecordDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Upload;

    public $userId;
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $userId = $this->userId;

            /*Delete default records*/
            DB::table('deposits')->where('user_id', $userId)->delete();
            DB::table('funds')->where('user_id', $userId)->delete();
            DB::table('payouts')->where('user_id', $userId)->delete();
            DB::table('transactions')->where('user_id', $userId)->delete();
            DB::table('user_kycs')->where('user_id', $userId)->delete();
            DB::table('user_logins')->where('user_id', $userId)->delete();
            DB::table('in_app_notifications')->where('in_app_notificationable_id', $userId)
                ->where('in_app_notificationable_type','App\Models\User')->delete();
            $this->deleteSupportTickets();

            /*Delete extra records*/
            DB::table('wallets')->where('user_id', $userId)->delete();
            DB::table('bill_pays')->where('user_id', $userId)->delete();
            DB::table('virtual_card_orders')->where('user_id', $userId)->delete();
            DB::table('virtual_card_transactions')->where('user_id', $userId)->delete();
            DB::table('exchanges')->where('user_id', $userId)->delete();
            DB::table('q_r_codes')->where('user_id', $userId)->delete();
            DB::table('invoices')->where('sender_id', $userId)->delete();

            DB::table('commission_entries')
                ->where(function ($query) use ($userId) {
                    $query->where('to_user', $userId)->orWhere('from_user', $userId);
                })
                ->delete();

            //where has sender_id & receiver_id
            $this->deleteService('transfers');
            $this->deleteService('request_money');
            $this->deleteService('escrows');
            $this->deleteService('redeem_codes');
            $this->deleteService('vouchers');

            /*Delete store-related records*/
            $this->deleteStoreData();

        });
    }

    protected function deleteSupportTickets()
    {
        $tickets = SupportTicket::with('messages.attachments')
            ->where('user_id', $this->userId)
            ->get();

        foreach ($tickets as $ticket) {
            foreach ($ticket->messages as $message) {
                foreach ($message->attachments as $attachment) {
                    $this->fileDelete($attachment->driver, $attachment->file);
                    $attachment->delete();
                }
            }
            $ticket->messages()->delete();
            $ticket->delete();
        }
    }

    protected function deleteStoreData()
    {
        $userId = $this->userId;

        $storeIds = DB::table('stores')->where('user_id', $userId)->pluck('id');
        $productIds = DB::table('store_products')->where('user_id', $userId)->pluck('id');
        $orderIds = DB::table('product_orders')
            ->where('user_id', $userId)->orWhereIn('store_id', $storeIds)->pluck('id');

        DB::table('store_product_attrs')->where('user_id', $userId)->delete();
        DB::table('store_product_images')->whereIn('product_id', $productIds)->delete();
        DB::table('store_product_stocks')->whereIn('product_id', $productIds)
            ->where('user_id', $userId)->delete();
        DB::table('store_products')->where('user_id', $userId)->delete();
        DB::table('store_categories')->where('user_id', $userId)->delete();
        DB::table('store_shippings')->where('user_id', $userId)->delete();
        DB::table('product_store_maps')->whereIn('store_id', $storeIds)->delete();
        DB::table('product_order_details')->whereIn('order_id', $orderIds)->delete();
        DB::table('product_orders')->whereIn('id', $orderIds)->delete();
        DB::table('seller_contacts')->where('user_id', $userId)->delete();
        DB::table('stores')->where('user_id', $userId)->delete();
    }

    public function deleteService(string $table): void
    {
        $userId = $this->userId;

        DB::table($table)
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->delete();
    }

}
