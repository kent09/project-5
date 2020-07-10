<div class="left-section">
			<center><a href="{{ url('/home') }}"><img src="{{ url('assets/images/fusedlogoor.png') }}"/></a></center>
			@if( Auth::user() )
        <div  class="left-navigation">
      <div class="panel-group" id="accordions">
      	<div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> Dashboard</a>
            </h4>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" class="collapsed haschilddrp"><i class="fa fa-paper-plane"></i> Send Documents</a>
            </h4>
          </div>
          <div id="collapse1" class="panel-collapse collapse">
            <div class="panel-body">
              <ul>
      	        <li><a href="{{ url('docs') }}"><i class="fa fa-folder"></i> Manage & Setup Documents</a></li>
      	        <li><a href="{{ url('/docs/history') }}"><i class="fa fa-history"></i> Document History</a></li>
      	        <li><a href="{{ url('docs/notifications') }}"><i class="fa fa-warning"></i> Document Error Log</a></li>
      	        <li><a href="{{ url('/docs/panda-setup-guide') }}"><i class="fa fa-info-circle"></i> Getting Started Video</a></li>
      	        <li><a href="{{ url('/docs/manage-panda-account') }}"><i class="fa fa-cog"></i> Manage PandaDoc Account</a></li>
      	        <li><a href="{{ url('docs/docusign') }}"><i class="fa fa-folder"></i> Docusign Manage Documents</a></li>
      	        <li><a href="{{ url('/docs/manage-docusign-account') }}"><i class="fa fa-cog"></i> Manage Docusign Account</a></li>
      	      </ul>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse2" class="collapsed haschilddrp"><i class="fa fa-globe"></i> Geographic Tools</a>
            </h4>
          </div>
          <div id="collapse2" class="panel-collapse collapse">
            <div class="panel-body">
              <ul>
                <li><a href="{{ url('scripts/postcode-based-owner') }}"><i class="fa fa-map-marker"></i> Postcode Based Owner</a></li>
                <li><a href="{{ url('scripts/postcode-contact-tagging') }}"><i class="fa fa-tag"></i> Postcode Contact Tagging</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" class="collapsed haschilddrp"><i class="fa fa-times-circle"></i> Xero Tools</a>
            </h4>
          </div>
            <div id="collapse3" class="panel-collapse collapse">
              <div class="panel-body">
              <ul>
                <li><a href="{{ url('scripts/xero-invoice-cron') }}"><i class="fa fa-file"></i> Copy Order To Xero Invoice</a></li>
                <li><a href="{{ url('scripts/xero-invoice-copy') }}"><i class="fa fa-list"></i> HTTP Xero Invoice Creator</a></li>
                <li><a href="{{ url('scripts/xero-invoice-copy') }}"><i class="fa fa-refresh"></i> Xero Invoice Sync</a></li>
                <li><a href="{{ url('/xero-account') }}"><i class="fa fa-cog"></i> Manage Xero Accounts</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" class="collapsed haschilddrp"><i class="fa fa-file"></i> Useful Scripts</a>
            </h4>
          </div>
          <div id="collapse4" class="panel-collapse collapse">
            <div class="panel-body">
              <ul>
                <li><a href="{{ url('scripts/move-opportunities') }}"><i class="fa fa-arrow-up"></i> Move Opportunities</a></li>
                <li><a href="{{ url('scripts/update-credit-cards') }}"><i class="fa fa-credit-card"></i> Update Credit Cards</a></li>
                <li><a href="{{ url('scripts/add-to-values') }}"><i class="fa fa-plus-circle"></i> Add To / Increment Fields</a></li>
                <li><a href="{{ url('scripts/names-from-orders') }}"><i class="fa fa-envelope"></i> Order Products To Field</a></li>
                <li><a href="{{ url('scripts/copy-values') }}"><i class="fa fa-copy"></i> Copy Values Between Fields</a></li>
                <li><a href="{{ url('scripts/calculate-dates') }}"><i class="fa fa-calendar"></i> Calculate & Store Dates</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse5" class="collapsed haschilddrp"><i class="fa fa-download"></i> Import Tools</a>
            </h4>
          </div>
          <div id="collapse5" class="panel-collapse collapse">
          <div class="panel-body">
            <ul>
              <li><a href="{{ url('csv-import') }}"><i class="fa fa-table"></i> Import CSV</a></li>
            </ul>
          </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse6" class="collapsed haschilddrp"><i class="fa fa-user"></i> My Account</a>
            </h4>
          </div>
          <div id="collapse6" class="panel-collapse collapse">
            <div class="panel-body">
              <ul>
                <li><a href="{{ url('/billing') }}"><i class="fa fa-credit-card"></i> Plans and Billing</a></li>
                <li><a href="{{ url('/change-password') }}"><i class="fa fa-key"></i> Change Password</a></li>
                <li><a href="{{ url('/account-settings') }}"><i class="fa fa-cog"></i> Account Settings</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a href="{{ url('/manage-accounts') }}"><i class="fa fa-cog"></i> Manage Infusionsoft Account</a>
              </h4>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a href="{{ url('/support') }}"><i class="fa fa-life-ring"></i> Support</a>
              </h4>
          </div>
          
        </div>
      </div>      
		</div>

        @endif
        </div>









        <div class="right-section">
        <ul class="top-nav">
        @if (Auth::guest())
          <li><a href="{{ url('/register') }}">Register</a></li>
        @else
          <li><a href="{{ url('/manage-accounts') }}">Manage INFS Accounts</a></li>
          <li><a href="{{ url('/logout') }}">Logout</a></li>
        @endif
      </ul>