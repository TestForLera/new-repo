@extends('layots')
@section('title', 'Заказы')
@section('content')
<div class="container py-4" style="margin-top:80px;">
    <div class="d-flex justify-content-between mb-3">
        <h2>Заказы</h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Назад</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Клиент</th><th>Телефон</th><th>Дата</th><th>Программа</th><th>Адрес</th><th>Статус</th></tr></thead>
            <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->user->name ?? '—' }}</td>
                    <td>{{ $order->user->phone ?? '—' }}</td>
                    <td>{{ $order->date?->format('d.m.Y') }} {{ $order->time }}</td>
                    <td>{{ $order->package->title ?? '—' }}</td>
                    <td>{{ $order->address }}</td>
                    <td>
                        <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach(\App\Models\Order::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $orders->links() }}
    </div></div>
</div>
@endsection
