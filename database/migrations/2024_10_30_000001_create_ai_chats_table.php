<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAiChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Builder::morphUsingUuids();

        Schema::create('ai_chats', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->foreignIdFor(User::class, 'user_id')->constrained()->onDelete('cascade');
            $table->morphs('chatable');
            $table->string('role');
            $table->text('content');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_chats');
    }
}
