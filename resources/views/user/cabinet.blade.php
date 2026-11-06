@extends('layots')

@section('title', 'Личный кабинет')

@section('content')
<div class="container py-5" style="margin-top: 80px;">
    <h2 class="mb-4">Личный кабинет</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="#profile" class="list-group-item list-group-item-action active">Профиль</a>
                <a href="#orders" class="list-group-item list-group-item-action">Мои заказы</a>
                <a href="#favorites" class="list-group-item list-group-item-action">Избранное</a>
                <a href="#reviews" class="list-group-item list-group-item-action">Отзывы</a>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Профиль -->
            <div id="profile" class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Личные данные</h5></div>
                <div class="card-body">
                    <form action="{{ route('update.profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ФИО</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Телефон</label>
                                <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Аватар</label>
                                @if($user->avatar)
                                    <div class="mb-2"><img src="{{ asset('storage/'.$user->avatar) }}" alt="" style="max-height:80px" class="rounded"></div>
                                @endif
                                <input type="file" name="avatar" class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить данные</button>
                    </form>

                    <hr class="my-4">
                    <h6>Сменить пароль</h6>
                    <form action="{{ route('change.password') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-4">
                            <input type="password" name="current_password" class="form-control" placeholder="Текущий пароль" required>
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="new_password" class="form-control" placeholder="Новый пароль" required>
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="Повтор пароля" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning btn-sm">Сменить пароль</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Заказы -->
            <div id="orders" class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Заказы анимационных программ</h5></div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <p class="text-muted mb-0">Заказов пока нет.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Дата</th>
                                        <th>Программа</th>
                                        <th>Статус</th>
                                        <th>Адрес</th>
                                        <th>Отзыв</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->date?->format('d.m.Y') }} {{ $order->time }}</td>
                                            <td>{{ $order->package->title ?? $order->package->name ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                                    {{ $order->status_label }}
                                                </span>
                                            </td>
                                            <td>{{ $order->address }}</td>
                                            <td>
                                                @if($order->status === 'completed')
                                                    @if($order->review)
                                                        <span class="text-success">★ {{ $order->review->rating }}</span>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $order->id }}">
                                                            Оставить отзыв
                                                        </button>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>

                                        @if($order->status === 'completed' && !$order->review)
                                        <div class="modal fade" id="reviewModal{{ $order->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('submit.review') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Отзыв по заказу #{{ $order->id }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Оценка</label>
                                                                <select name="rating" class="form-select" required>
                                                                    @for($i=5;$i>=1;$i--)
                                                                        <option value="{{ $i }}">{{ $i }} ★</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Комментарий</label>
                                                                <textarea name="comment" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Отправить</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Избранное -->
            <div id="favorites" class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Избранное</h5></div>
                <div class="card-body">
                    @if($favorites->isEmpty())
                        <p class="text-muted mb-0">В избранном пока пусто.</p>
                    @else
                        <div class="row g-3">
                            @foreach($favorites as $fav)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($fav->package)
                                                <strong>{{ $fav->package->title ?? $fav->package->name }}</strong>
                                                <div class="small text-muted">Пакет / программа</div>
                                            @elseif($fav->hero)
                                                <strong>{{ $fav->hero->name }}</strong>
                                                <div class="small text-muted">Герой</div>
                                            @elseif($fav->service)
                                                <strong>{{ $fav->service->title ?? $fav->service->name }}</strong>
                                                <div class="small text-muted">Услуга</div>
                                            @endif
                                        </div>
                                        <form action="{{ route('favorite.remove', $fav) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Мои отзывы -->
            <div id="reviews" class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Мои отзывы</h5></div>
                <div class="card-body">
                    @if($reviews->isEmpty())
                        <p class="text-muted mb-0">Вы ещё не оставляли отзывов.</p>
                    @else
                        @foreach($reviews as $review)
                            <div class="border-bottom py-2">
                                <div class="fw-bold">★ {{ $review->rating }}</div>
                                <div>{{ $review->text }}</div>
                                <div class="small text-muted">{{ $review->created_at?->format('d.m.Y') }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
