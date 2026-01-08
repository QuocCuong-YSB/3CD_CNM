<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendCodeRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Http\Resources\UserResource;
use App\Mail\SendMail;
use App\Models\User;
use App\Models\Verification;
use Google\Client as GoogleClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $dataimg = [];
        if ($request->hasFile('avatar')) {
            foreach ($request->file('avatar') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();

                $file->move(public_path('avatars'), $filename);

                $dataimg[] = 'avatars/' . $filename;
            }
        }

        $data['password'] = bcrypt($data['password']);
        $data['avatar'] = json_encode($dataimg);
        Verification::where('email', $data['email'])->delete();
        $code = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        $record = Verification::create([
            'email' => $data['email'],
            'code' => $code,
            'user_data' => $data,
            'expires_at' => $expiresAt,
            'last_sent_at' => Carbon::now()
        ]);

        Mail::to($record->email)->send(new SendMail($record->code));

        return response()->json([
            'message' => 'Mã xác thực đã gửi đến email của bạn.',
            'requireVerification' => true,
            'email' => $data['email']
        ], 200);
    }
    public function verifyCode(VerifyCodeRequest $request)
    {
        $request->validated();

        $record = Verification::where('email', $request->email)->first();

        if (!$record || Carbon::now()->gt($record->expires_at)) {
            return response()->json(['error' => 'Mã xác thực không tồn tại hoặc đã hết hạn.'], 400);
        }

        if ((int) $record->code !== (int) $request->code) {
            return response()->json(['error' => 'Mã xác thực không chính xác.'], 400);
        }

        $user = User::create($record->user_data);

        $record->delete();

        return response()->json([
            'message' => 'Đăng ký thành công!',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'address', 'avatar', 'level', 'id_country'])
        ], 200);
    }

    public function resendCode(ResendCodeRequest $request)
    {
        $request->validated();
        $record = Verification::where('email', $request->email)->first();

        if (!$record) {
            return response()->json(['error' => 'Không tìm thấy yêu cầu đăng ký. Vui lòng đăng ký lại.'], 400);
        }

        $now = Carbon::now();
        $diff = $now->diffInSeconds($record->last_sent_at);
        if ($diff < 60) {
            return response()->json(['error' => "Vui lòng đợi " . (60 - $diff) . " giây để gửi lại mã."], 429);
        }

        $code = rand(100000, 999999);
        $record->code = $code;
        $record->last_sent_at = $now;
        $record->expires_at = Carbon::now()->addMinutes(10);
        $record->save();

        Mail::to($record->email)->send(new SendMail($record->code));

        return response()->json(['message' => 'Đã gửi lại mã xác thực.'], 200);
    }
    public function login(LoginRequest $request)
    {
        $userRequest = $request->validated();
        $user = User::where('email', $userRequest['email'])->first();
        if (!$user || !Hash::check($userRequest['password'], $user->password)) {
            return response()->json([
                'message' => 'Email hoặc mật khẩu không đúng',
            ], 401);
        }
        if ((int) $user->level !== (int) $userRequest['level']) {
            return response()->json([
                'message' => 'Email hoặc mật khẩu không đúng',
            ], 403);
        }
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Tài khoản đã bị khóa'
            ], 403);
        }
        $tokenInstance = $user->createToken('access-token');
        $token = $tokenInstance->plainTextToken;
        $tokenInstance->accessToken->expires_at = Carbon::now()->addHour();
        $tokenInstance->accessToken->save();

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ], 200);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ], 200);
    }

    public function loginWithGoogle(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string'
            ]);

            $client = new GoogleClient([
                'client_id' => env('GOOGLE_CLIENT_ID')
            ]);

            // Verify token với Google
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                return response()->json([
                    'message' => 'Token Google không hợp lệ'
                ], 401);
            }

            // Thông tin user từ Google
            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'] ?? '';
            $avatar = $payload['picture'] ?? '';

            // Tìm user theo google_id hoặc email
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            if (!$user) {
                // Chưa có → tạo mới
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'provider' => 'google',
                    'avatar' => $avatar,
                    'password' => '',
                    'level' => 0,
                    'is_active' => 1,
                    'address' => '',
                    'id_country' => 1
                ]);
            } else {
                // Có rồi nhưng chưa liên kết Google
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleId,
                        'provider' => 'google',
                        'avatar' => $avatar
                    ]);
                }
            }

            // Tạo token đăng nhập (Sanctum)
            $tokenInstance = $user->createToken('access-token');
            $token = $tokenInstance->plainTextToken;

            return response()->json([
                'message' => 'Login Google thành công',
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi hệ thống khi đăng nhập Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

