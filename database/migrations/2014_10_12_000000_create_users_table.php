<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // // mysql
            // $table->boolean('is_admin')->nullable();
            // $table->boolean('is_spoc')->nullable();
            // $table->boolean('is_notify')->nullable();
            // $table->boolean('not_notify_my_action')->nullable();
            
            // mssql
            $table->boolean('is_admin')->default(0);
            $table->boolean('is_spoc')->default(0);
            $table->boolean('is_notify')->default(0);
            $table->boolean('not_notify_my_action')->default(0);
            
            $table->rememberToken();
            $table->timestamps();
            $table->boolean('is_activated')->default(0);
        });

        // insert system app user (no person) for some automation features
        DB::table('users')->insert([
            // 'name' => env('APP_NAME'), 
            'name' => env('APP_NAME'),     // New ticket from customer is auto. assigned to the user "Nobody"
            'email' => 'domain@localhost',
            'password' => 'no_password'
        ]);

        // insert global admin for 1 admin person , e.g who installed this app
        $is_activated = 0;
        if( null != env('APP_GLOBAL_ADMIN_EMAIL') ) {
            $global_admin_name = null == env('APP_GLOBAL_NAME') ? env('APP_NAME').'_Installer' : env('APP_GLOBAL_NAME');
            DB::table('users')->insert([
                'name' => $global_admin_name,     
                'email' => env('APP_GLOBAL_ADMIN_EMAIL'),
                'password' => 'no_password',
                'is_activated' => '1',
            ]);
        } 
        // mysql
        DB::table('users')->update(['is_admin' => '1']); // for app user for any automated feature
        // mssql
        // DB::table('users')->update(['is_admin' => 'true']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
