  <nav class="navbar subheader">
    <ul class="nav navbar-nav navbar-right nav-item">
      <li><a href="/manageaccounts">Linked Accounts</a></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Your Account <span class="caret"></span></a>
        <ul class="dropdown-menu dropdownUser">
          <li class="dropdownUserInfo">{{ Auth::user()->userAddress->first_name }} {{ Auth::user()->userAddress->last_name }}</li>
          <li class="dropdownUserInfo">{{ Auth::user()->email }}</li>
          <li role="separator" class="divider"></li>
          <li class="dropdownItem"><a href="//{{env('FUSEDSUITE_ACCOUNT_SUBDOMAIN').'.'.env('APP_URL')}}/billing"><i class="fa fa-credit-card"></i> Plans and Billing</a></li>
          <li class="dropdownItem"><a href="/changepassword"><i class="fa fa-key"></i> Change Password</a></li>
          <li role="separator" class="divider"></li>
          <li class="dropdownItem">
              <form id="logout-form" action="{{ route('logout') }}" method="POST">
                  {{ csrf_field() }}
                  <button type="submit"><i class="fa fa-sign-out"></i> Logout</button>
              </form>
            <!-- <a href="{{ url('/logout') }}"><i class="fa fa-sign-out"></i> Logout</a> -->
          </li>
        </ul>
      </li>
      <li><a href="//{{env('FUSEDSUITE_ACCOUNT_SUBDOMAIN').'.'.env('APP_URL')}}/support"> Help & Support</a></li>
    </ul>
  </nav>