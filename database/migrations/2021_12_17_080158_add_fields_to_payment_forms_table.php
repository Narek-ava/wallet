<?php

use App\Models\PaymentForm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPaymentFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_forms', function (Blueprint $table) {
            $table->string('name')->after('id')->unique();

            $table->boolean('kyc')->default(false);
            $table->uuid('rate_template_id')->nullable()->after('wallet_provider_id');
            $table->foreign('rate_template_id')->references('id')->on('rate_templates')->nullOnDelete();

            $table->dropForeign('payment_forms_c_profile_id_foreign');
            $table->uuid('c_profile_id')->nullable()->change();
            $table->foreign('c_profile_id')->references('id')->on('c_profiles')->nullOnDelete();

        });

        foreach (PaymentForm::all() as $paymentForm) {
            $paymentForm->name = $paymentForm->cProfile->getFullName();
            $paymentForm->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_forms', function (Blueprint $table) {
            $table->dropColumn('name');

            $table->dropForeign('payment_forms_rate_template_id_foreign');
            $table->dropColumn('rate_template_id');
            $table->dropColumn('kyc');

            $table->dropForeign('payment_forms_c_profile_id_foreign');
            $table->uuid('c_profile_id')->change();
            $table->foreign('c_profile_id')->references('id')->on('c_profiles')->cascadeOnDelete();
        });
    }
}
