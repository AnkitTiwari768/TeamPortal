 


<button type="submit" title="Save" id="submit" value="Submit" class="btn btn-primary" autocomplete="off">
            @isset($id)
        <span class="btn-label"><i class="fa fa-save"></i></span> {{ __('message.update') }}
                @else 
                <span class="btn-label"><i class="fa fa-save"></i></span> {{ __('message.submit') }}
                @endisset
            </button>