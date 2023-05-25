<div class="form-label-group">
    <label for="input-phone-no-part">Phone number</label>
    <div class="row pl-3 pr-3">
        <div class="form-group col-12 col-sm-5 pl-0 pr-0 pr-sm-2">
            <select name="phone_cc_part" class="select-phone-cc form-control" style="width: 100%;">
            </select>
        </div>
        <input name="phone_no_part" type="text" id="input-phone-no-part" class="form-control col-12 col-sm-7"
               placeholder="" required>
    </div>
    @error('phone_cc_part')
        <p class="error-text">{{ $message }}</p>
    @enderror
    @error('phone_no_part')
        <p class="error-text">{{ $message }}</p>
    @enderror
</div>

