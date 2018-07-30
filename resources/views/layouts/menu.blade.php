{{-- All Users --}}
<li class="{{ Request::is('users*') ? 'active' : '' }}">
	<a href="{!! route('users.show',['id'=> Auth::user()->id]) !!}">
		<i class="fa fa-edit"></i><span>My Profile</span>
	</a>
</li>
<li class="{{ Request::is('accounts*') ? 'active' : ''}}">
	<a href="{!! route('accounts.show') !!}">
		<i class="fa fa-edit"></i><span>My Account</span>
	</a>
</li>
<li class="{{ Request::is('transactions*') ? 'active' : '' }}">
    <a href="{!! route('transactions.index') !!}"><i class="fa fa-edit">
        </i><span>Transactions</span>
    </a>
</li>

{{-- Webmasters --}}
@if(Auth::user()->role_id < 4)
	<li class="{{ Request::is('qrcodes*') ? 'active' : '' }}">
		<a href="{!! route('qrcodes.index') !!}">
			<i class="fa fa-edit"></i><span>Qrcodes</span>
		</a>
	</li>
@endif
