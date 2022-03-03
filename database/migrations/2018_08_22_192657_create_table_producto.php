<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProducto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigobarra', 100);
            $table->string('nombre', 100);
            $table->string('abreviatura', 100);
            $table->integer('categoria_id')->unsigned();
            $table->integer('marca_id')->unsigned();
            $table->integer('unidad_id')->unsigned();
            $table->decimal('stockminimo', 10,2);
            $table->decimal('preciocompra', 10,2);
            $table->decimal('precioventa', 10,2);
            $table->foreign('categoria_id')->references('id')->on('categoria')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('marca_id')->references('id')->on('marca')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('unidad_id')->references('id')->on('unidad')->onDelete('restrict')->onUpdate('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('producto');
    }
}
