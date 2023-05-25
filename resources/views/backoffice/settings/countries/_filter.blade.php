<form action="" class="col-md-12">
    <div class="row mt-3">
        <div class="col-md-3 pt-2 text-center">
            <input type="text" name="name" value="{{ request()->name }}" placeholder="Country name">
        </div>
        <div class="col-md-2 pt-2 text-center">
            <input type="text" name="code" value="{{ request()->code }}" placeholder="Country code">
        </div>
        <div class="col-md-2 pt-2 text-center">
            <input type="text" name="phone_code" value="{{ request()->phone_code }}" placeholder="Phone code">
        </div>
        <div class="col-md-2 text-center">
            <select class="w-100" name="banned" id="country">
                <option value=""></option>
                @foreach(\App\Models\Country::BANNED_NAMES as $key => $name)
                    <option value="{{ $key }}" {{ isset(request()->banned) && request()->banned == $key ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 text-center">
            <button class="btn btn-lg btn-primary themeBtn register-buttons round-border mb-0 mb-md-0" type="submit">
                Find
            </button>
        </div>
    </div>
</form>
