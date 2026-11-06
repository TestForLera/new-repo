<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\Package;
use App\Models\Hero;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orders = Order::with(['package', 'review'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        $favorites = Favorite::with(['package', 'hero', 'service'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        $reviews = Review::where('user_id', $user->id)->latest()->get();

        return view('user.cabinet', compact('user', 'orders', 'favorites', 'reviews'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:30',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Профиль обновлён!');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Неверный текущий пароль']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Пароль изменён!');
    }

    public function submitReview(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ]);

        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->firstOrFail();

        if ($order->review) {
            return back()->withErrors(['order_id' => 'Отзыв по этому заказу уже оставлен']);
        }

        Review::create([
            'user_id' => auth()->id(),
            'order_id' => $order->id,
            'package_id' => $order->package_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'content' => $validated['comment'],
            'author_name' => auth()->user()->name,
            'category' => 'order',
            'is_published' => true,
        ]);

        return back()->with('success', 'Отзыв добавлен!');
    }

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'service_id' => 'nullable|exists:services,id',
            'date' => 'required|date',
            'time' => 'required|string',
            'address' => 'required|string|max:500',
            'comment' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        if (!empty($validated['package_id'])) {
            $package = Package::find($validated['package_id']);
            $validated['price'] = $package?->discounted_price ?? $package?->original_price;
        }

        Order::create($validated);

        return back()->with('success', 'Заявка создана!');
    }

    public function toggleFavorite(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'hero_id' => 'nullable|exists:heroes,id',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $query = Favorite::where('user_id', auth()->id());

        if (!empty($validated['package_id'])) {
            $query->where('package_id', $validated['package_id']);
        } elseif (!empty($validated['hero_id'])) {
            $query->where('hero_id', $validated['hero_id']);
        } elseif (!empty($validated['service_id'])) {
            $query->where('service_id', $validated['service_id']);
        } else {
            return back()->withErrors(['favorite' => 'Не указан объект избранного']);
        }

        $existing = $query->first();
        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Удалено из избранного');
        }

        Favorite::create(array_merge(['user_id' => auth()->id()], $validated));
        return back()->with('success', 'Добавлено в избранное');
    }

    public function removeFavorite(Favorite $favorite)
    {
        if ($favorite->user_id !== auth()->id()) {
            abort(403);
        }
        $favorite->delete();
        return back()->with('success', 'Удалено из избранного');
    }
}
