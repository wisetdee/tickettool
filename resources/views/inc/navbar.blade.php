{{-- <nav class="navbar navbar-expand-md navbar-dark bg-dark">
  <a class="navbar-brand" href="/">{{config('app.name','ibmticket')}}</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="//about">about</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="//services">services</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="//posts">Blog</a>
      </li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><a href="//posts/create">Create Post</a></li>
    </ul>
  </div>
</nav> --}}

<nav class="navbar navbar-dark bg-dark navbar-expand-md navbar-light navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'ibmticket') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

            </ul>

            <ul class="navbar-nav mr-auto">
              <li class="nav-item active">
                <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
              </li>
              @if(Auth::user()!=null) 
                <li class="nav-item active">
                  <a class="nav-link" href="/tickets">Ticket<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item active">
                  <a class="nav-link" href="/about">IBMSLA</a>
                </li>
                {{-- <li class="nav-item">
                  <a class="nav-link" href="/services">services</a>
                </li> --}}
                {{-- <li class="nav-item">
                  <a class="nav-link" href="/posts">Blog</a>
                </li> --}}
                @if(auth()->user()->is_admin)
                  <li class="nav-item">
                      <a class="nav-link" href="/imap">FetchMails</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="/check_overdue">CheckOverdue</a>
                  </li>
                @endif
              @endif
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    {{-- @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif --}}
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>
                        
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>
                            <a class="dropdown-item" href="/changepassword">
                                Change Password
                            </a>
                            {{-- <a class="dropdown-item" href="/dashboard">Dashboard</a> --}}
                            @if(auth()->user()->is_admin)
                                <a class="dropdown-item" href="/users">Admin Panel</a>
                            @endif
                            @if(auth()->user()->is_admin)
                                <a class="dropdown-item" href="/ticket_config">Ticket Config</a>
                            @endif
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
{{-- NOT USED TO BE DELETED , cause it makes error in vuejs --}}
{{-- <style>
.navbar-default .navbar-nav > .active > a,
.navbar-default .navbar-nav > .active > a:hover,
.navbar-default .navbar-nav > .active > a:focus {
color: #333333; /* <—– change your color here*/
background-color: transparent;
}
</style> --}}