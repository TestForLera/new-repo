<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalUsers' => User::where('role', 'user')->count(),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'totalReviews' => Review::count(),
        ];

        $recentOrders = Order::with(['user', 'package'])->latest()->take(10)->get();
        $users = User::where('role', 'user')->latest()->take(20)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'users'));
    }

    public function orders()
    {
        $orders = Order::with(['user', 'package'])->latest()->paginate(20);
        return view('admin.orders', compact('orders'));
    }

    public function users()
    {
        $users = User::withCount('orders')->latest()->paginate(30);
        return view('admin.users', compact('users'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $order->update($validated);

        return back()->with('success', 'Статус обновлён!');
    }
}
