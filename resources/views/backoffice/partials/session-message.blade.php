@foreach(['success', 'warning', 'error'] as $session)
    @if (\Session::has($session))
        <div class="alert alert-{{$session}} alert-dismissible fade show" role="alert">
            <h4>
                {!! \Session::get($session) !!}
            </h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@endforeach
