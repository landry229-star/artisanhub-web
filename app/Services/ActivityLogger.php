<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, ?Model $subject = null, array $properties = []): void
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id'      => Auth::id(),
                'action'       => $action,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'properties'   => json_encode($properties),
                'ip_address'   => Request::ip(),
                'user_agent'   => substr(Request::userAgent() ?? '', 0, 255),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ActivityLogger: " . $e->getMessage());
        }
    }

    public static function loginSuccess(string $email): void   { self::log('auth.login',          null, ['email' => $email]); }
    public static function loginFailed(string $email): void    { self::log('auth.login_failed',    null, ['email' => $email]); }
    public static function orderAccepted($order): void         { self::log('order.accepted',       $order); }
    public static function orderDisputed($order): void         { self::log('order.disputed',       $order); }
    public static function userSuspended($user): void          { self::log('user.suspended',       $user,  ['email' => $user->email]); }
    public static function userVerified($user): void           { self::log('user.verified',        $user,  ['email' => $user->email]); }
    public static function paymentCompleted($order): void      { self::log('payment.completed',    $order, ['amount' => $order->payment?->amount]); }
}
