<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
class CustomersController extends Controller
{
    // Đăng ký người dùng mới mà không tạo token
    public function register(Request $request)
    {
        try {
            // Xác thực dữ liệu đầu vào
            Log::info('Request data:', $request->all());
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            // Tạo người dùng mới
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            return response()->json(['message' => 'User registered successfully!', 'user' => $user], 201);
        } catch (\Exception $e) {
            // Ghi lại lỗi vào log
            Log::error('Registration error: ' . $e->getMessage());
    
            // Trả về thông báo lỗi cho client
            return response()->json([
                'error' => 'Có lỗi xảy ra trong quá trình đăng ký.',
                'details' => $e->getMessage() // In chi tiết lỗi ra JSON
            ], 500);
        }
        
    }
    // Đăng nhập mà không tạo token
    public function login(Request $request)
    { try {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Xác thực thông tin đăng nhập
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Lấy ID của người dùng hiện tại từ Auth
        $userId = Auth::id();

        // Tải lại đối tượng User từ cơ sở dữ liệu
        $user = User::find($userId);

        // Tạo token tùy chỉnh (có thể tùy chỉnh độ dài token)
        $token = bin2hex(random_bytes(40)); // Tạo chuỗi token ngẫu nhiên

        // Lưu token vào cột custom_token của bảng users
        $user->remember_token = $token;
        $user->save(); // Lưu đối tượng người dùng lại với token mới

        // Trả về token và thông tin người dùng
        return response()->json([
            'message' => 'User logged in successfully!',
            'user' => $user,
            'access_token' => $token, // Trả về token tùy chỉnh
            'token_type' => 'Bearer',
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Login failed: ' . $e->getMessage()], 500);
    }
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        // Lấy người dùng hiện tại
        $user = $request->user();
    
        if ($user) {
            // Xóa token hiện tại
            $user->currentAccessToken()->delete(); // Xóa token hiện tại của người dùng
    
            return response()->json([
                'message' => 'Đăng xuất thành công!',
                'status' => true,
            ]);
        }
    
        return response()->json([
            'message' => 'Không thể tìm thấy người dùng để đăng xuất!',
            'status' => false,
        ], 404);
    }
    public function update(Request $request, $id)
    {
        try {
            // Tìm người dùng theo ID
            $user = User::findOrFail($id);

            // Xác thực dữ liệu đầu vào
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:8|confirmed',
            ]);

            // Cập nhật thông tin người dùng
            $user->name = $request->name ?? $user->name;
            $user->email = $request->email ?? $user->email;

            // Nếu người dùng cập nhật mật khẩu thì mã hóa lại mật khẩu
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            // Lưu thông tin người dùng
            $user->save();

            return response()->json(['message' => 'User updated successfully!', 'user' => $user], 200);
        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());

            return response()->json(['error' => 'Cập nhật thất bại: ' . $e->getMessage()], 500);
        }
    }

    // Xóa người dùng
    public function destroy($id)
    {
        try {
            // Tìm người dùng theo ID
            $user = User::findOrFail($id);

            // Xóa người dùng
            $user->delete();

            return response()->json(['message' => 'User deleted successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());

            return response()->json(['error' => 'Xóa người dùng thất bại: ' . $e->getMessage()], 500);
        }
    }
}
