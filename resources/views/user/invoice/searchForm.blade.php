

<x-offcanvas.filter-btn/>
<x-offcanvas.layout id="filterOffcanvas">

    <x-offcanvas.filter-field label="Email" name="email" type="text" placeholder="doe@mail.net"/>
    <x-offcanvas.filter-field label="Transaction ID" name="hash_slug" type="text" placeholder="transaction id"/>
    <x-offcanvas.filter-field label="Minimum Amount" name="min" type="text" placeholder="10" atcoff/>
    <x-offcanvas.filter-field label="Maximum Amount" name="max" type="text" placeholder="200" atcoff/>

    <x-offcanvas.filter-field label="Currency" name="currency_id" type="select"
                    :options="$currencies->pluck('code', 'id')->toArray()" />

    <x-offcanvas.filter-field label="Status" name="status" type="select"
            :options="['paid' => 'Paid', 'unpaid' => 'Unpaid', 'rejected' => 'Rejected']"
            :indicators="[ 'paid'=>'bg-success', 'unpaid'=>'bg-warning','rejected'=>'bg-danger']"
    />

    <x-offcanvas.filter-field label="Date" name="created_at" type="date" />

</x-offcanvas.layout>

