@extends(userLayout())

@section('title',__('Api Documentation'))

@section('content')

    <div class="content {{ containerClass() }}" id="api-app">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('merchant.dashboard')}}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Business Api')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('API Documentation Reference')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <h3>@lang('Getting Started')</h3>
                <p>
                    @lang('This document explains how to successfully call the API with your app and get an
                    successful call. These endpoints helps you create and manage wallets.')
                </p>
                <p>
                    <x-badge text="Base Url" /><i>{{url('/')}}/api/</i>
                </p>
            </div>
            <div class="col-12 col-md-6 col-lg-6">
                <h2 class="section-title mt-0">@lang('Initiate Payment')</h2>
                <p>
                    @lang('To initiate the payment process follow the example code and be careful with the parameters.
                    Use this guide to make sure your chosen payment methods work for your business and to determine how you want to add payment methods.')
                </p>
                <p>
                    <x-badge text="Post" type="success"/><i>initiate/payment</i>
                </p>
                <hr/>
                <h5 class="mb-4">@lang('Body Params')</h5>
                <div class="row ">
                    <div class="col-12">
                        <p>
                            <b>currency<span class="text-danger">*</span></b>
                            <x-badge text="string" />
                        </p>
                    </div>
                    <div class="col-md-8">
                        <p class="mb-0">
                            @lang('Currency Code, Must be in Upper Case. e.g. USD, EUR')
                        </p>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="USD"/>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-12">
                        <p>
                            <b>amount<span class="text-danger">*</span></b>
                            <x-badge text="int32" />
                        </p>
                    </div>
                    <div class="col-md-8">
                        <p class="mb-0">
                            @lang('The amount you want to transaction')
                        </p>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="100"/>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-12">
                        <p>
                            <b>ipn_url<span class="text-danger">*</span></b>
                            <x-badge text="url" />
                        </p>
                    </div>
                    <div class="col-md-8">
                        <p class="mb-0">@lang('Instant payment notification url')</p>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="https://bugfinder.net/"/>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-12">
                        <p>
                            <b>callback_url<span class="text-danger">*</span></b>
                            <x-badge text="url" />
                        </p>
                    </div>
                    <div class="col-md-8">
                        <p class="mb-0">@lang('This url you should be redirect after successful payment')</p>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="https://bugfinder.net/"/>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-12">
                        <p>
                            <b>order_id<span class="text-danger">*</span></b>
                            <x-badge text="string" />
                        </p>
                    </div>
                    <div class="col-md-8">
                        <p class="mb-0">@lang('This is unique id generate for transaction. Its length should be 10 to 20 characters')</p>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="562589636985"/>
                    </div>
                </div>
                <hr/>
                <div id="accordion">
                    <div class="accordion">
                        <div class="accordion-header collapsed" role="button"
                             data-bs-toggle="collapse" data-bs-target="#panel-body-1"
                             aria-expanded="false">
                            <h4>@lang('Optional Parameters') <i class="bi bi-chevron-right d-inline"></i></h4>

                        </div>
                        <div
                            class="accordion-body collapse px-0 pt-4"
                            id="panel-body-1"
                            data-bs-parent="#accordion">
                            <div class="row">
                                <div class="col-12">
                                    <p>
                                        <b>customer_name</b>
                                        <x-badge text="string" />
                                    </p>
                                </div>
                                <div class="col-md-8">
                                    <p class="mb-0">
                                        @lang('The person name who make want to transaction. Name should no be greater than 20 characters')
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" value="John Doe"/>
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div class="col-12">
                                    <p>
                                        <b>customer_email</b>
                                        <x-badge text="string" />
                                    </p>
                                </div>
                                <div class="col-md-8">
                                    <p class="mb-0">
                                        @lang('The person email who make want to transaction.')
                                    </p>
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div class="col-12">
                                    <p>
                                        <b>description</b>
                                        <x-badge text="string" />
                                    </p>
                                </div>
                                <div class="col-md-8">
                                    <p class="mb-0">
                                        @lang('Details of your payment or transaction. Details should no be greater than 500 characters')
                                    </p>
                                </div>
                            </div>
                            <hr/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-6">
                <div class="card mb-2">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <ul class="nav nav-tabs" id="myTab2" role="tablist">
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    id="home-tab2"
                                    data-bs-toggle="tab"
                                    href="#node-js"
                                    role="tab"
                                    aria-controls="home"
                                    aria-selected="true">@lang('cURL')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="profile-tab2"
                                    data-bs-toggle="tab"
                                    href="#php"
                                    role="tab"
                                    aria-controls="profile"
                                    aria-selected="false">@lang('PHP')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="contact-tab2"
                                    data-bs-toggle="tab"
                                    href="#ruby"
                                    role="tab"
                                    aria-controls="contact"
                                    aria-selected="false">@lang('RUBY')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="node-tab2"
                                    data-bs-toggle="tab"
                                    href="#node"
                                    role="tab"
                                    aria-controls="contact"
                                    aria-selected="false">@lang('NODE JS')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="python-tab2"
                                    data-bs-toggle="tab"
                                    href="#python"
                                    role="tab"
                                    aria-controls="contact"
                                    aria-selected="false">@lang('PYTHON')</a>
                            </li>
                        </ul>
                        <a href="javascript:void(0)" class="btn btn-icon btn-primary ml-auto copy-btn">
                            <i class="bi-clipboard"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="tab-content tab-bordered" id="myTab3Content">
                            <div
                                class="tab-pane fade show active"
                                id="node-js"
                                role="tabpanel"
                                aria-labelledby="home-tab2"
                            >
                                       <pre>
<code id="copycurl1">
curl --location --request POST 'https://bugfinder.net/api/payment/initiate' \
--header 'ApiKey: 9a5cd8e5beb32c70400bfdcf5f576562bd09202d' \
--header 'SecretKey: f2cc7f986a81b14525807a37a6a89ec99b8a82b6' \
--form 'currency="USD"' \
--form 'amount="100"' \
--form 'ipn_url="https://bugfinder.net/"' \
--form 'callback_url="https://bugfinder.net/"' \
--form 'order_id="256985412569"' \
--form 'meta.customer_name="John Doe"' \
--form 'meta.customer_email="example@gmail.com"' \
--form 'meta.description=""'
</code>
                                    </pre>
                            </div>
                            <div class="tab-pane fade" id="php" role="tabpanel" aria-labelledby="profile-tab2">
                                       <pre>
<code id="copyphp1">
?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://bugfinder.net/api/payment/initiate',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('currency' => 'USD','amount' => '100','ipn_url' => 'https://bugfinder.net/','callback_url' => 'https://bugfinder.net/','order_id' => '256985412569','meta.customer_name' => 'John Doe','meta.customer_email' => 'example@gmail.com','meta.description' => ''),
  CURLOPT_HTTPHEADER => array(
    'ApiKey: 9a5cd8e5beb32c70400bfdcf5f576562bd09202d',
    'SecretKey: f2cc7f986a81b14525807a37a6a89ec99b8a82b6'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

</code>

</pre>
                            </div>
                            <div class="tab-pane fade" id="ruby" role="tabpanel" aria-labelledby="node-tab2">
                                       <pre>
<code id="copyruby1">
require "uri"
require "net/http"

url = URI("https://bugfinder.net/api/payment/initiate")

http = Net::HTTP.new(url.host, url.port);
request = Net::HTTP::Post.new(url)
request["ApiKey"] = "9a5cd8e5beb32c70400bfdcf5f576562bd09202d"
request["SecretKey"] = "f2cc7f986a81b14525807a37a6a89ec99b8a82b6"
form_data = [['currency', 'USD'],['amount', '100'],['ipn_url', 'https://bugfinder.net/'],['callback_url', 'https://bugfinder.net/'],['order_id', '256985412569'],['meta.customer_name', 'John Doe'],['meta.customer_email', 'example@gmail.com'],['meta.description', '']]
request.set_form form_data, 'multipart/form-data'
response = http.request(request)
puts response.read_body
</code>

</pre>
                            </div>
                            <div class="tab-pane fade" id="node" role="tabpanel" aria-labelledby="contact-tab3">
                                       <pre>
<code id="copynode1">
var request = require('request');
var options = {
  'method': 'POST',
  'url': 'https://bugfinder.net/payment/initiate',
  'headers': {
    'ApiKey': '9a5cd8e5beb32c70400bfdcf5f576562bd09202d',
    'SecretKey': 'f2cc7f986a81b14525807a37a6a89ec99b8a82b6'
  },
  formData: {
    'currency': 'USD',
    'amount': '100',
    'ipn_url': 'https://bugfinder.net/',
    'callback_url': 'https://bugfinder.net/',
    'order_id': '256985412569',
    'meta.customer_name': 'John Doe',
    'meta.customer_email': 'example@gmail.com',
    'meta.description': ''
  }
};
request(options, function (error, response) {
  if (error) throw new Error(error);
  console.log(response.body);
});

</code>

</pre>
                            </div>
                            <div class="tab-pane fade" id="python" role="tabpanel"
                                 aria-labelledby="python-tab3">
                                       <pre>
<code id="copypython1">
import requests

url = "https://bugfinder.net/payment/initiate"

payload={'currency': 'USD',
'amount': '100',
'ipn_url': 'https://bugfinder.net/',
'callback_url': 'https://bugfinder.net/',
'order_id': '256985412569',
'meta.customer_name': 'John Doe',
'meta.customer_email': 'example@gmail.com',
'meta.description': ''}
files=[

]
headers = {
  'ApiKey': '9a5cd8e5beb32c70400bfdcf5f576562bd09202d',
  'SecretKey': 'f2cc7f986a81b14525807a37a6a89ec99b8a82b6'
}

response = requests.request("POST", url, headers=headers, data=payload, files=files)

print(response.text)


</code>

</pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <!-- <h4>Bordered Tab</h4> -->

                        <ul class="nav nav-tabs" id="myTab3" role="tablist">
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    id="home-tab2"
                                    data-bs-toggle="tab"
                                    href="#success-code"
                                    role="tab"
                                    aria-controls="home"
                                    aria-selected="true">@lang('200 OK')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="profile-tab2"
                                    data-bs-toggle="tab"
                                    href="#bad-request"
                                    role="tab"
                                    aria-controls="profile"
                                    aria-selected="false">@lang('400 Bad Request')</a>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-icon btn-primary ml-auto copy-btn">
                            <i class="bi-clipboard"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="tab-content tab-bordered" id="myTab3Content">
                            <div
                                class="tab-pane fade show active"
                                id="success-code"
                                role="tabpanel"
                                aria-labelledby="home-tab2"
                            >
                                       <pre>
<code id="success1">
{
    "status": "success",
    "data": {
        "id": "f8f2ec61-3719-4d64-b185-4951b5f71f9d",
        "currency": "USD",
        "amount": "100",
        "order_id": "256985412569",
        "ipn_url": "https://bugfinder.net/",
        "callback_url": "https://bugfinder.net/",
        "meta": {
            "customer_name": null,
            "customer_email": null,
            "description": null
        },
        "redirect_url": "https://bugfinder.net/make/payment/test/f8f2ec61-3719-4d64-b185-4951b5f71f9d"
    }
}
</code>
                                    </pre>
                            </div>
                            <div
                                class="tab-pane fade"
                                id="bad-request"
                                role="tabpanel"
                                aria-labelledby="profile-tab2"
                            >
										<pre><code>{
    "status": "error",
    "error": [
        "The currency field is required.",
        "The amount field is required.",
        "The ipn url field is required.",
        "The callback url field is required.",
        "The order id field is required.",
        "The api key field is required.",
        "The secret key field is required."
    ]
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-6 mt-5">
                <h2 class="section-title mt-0">@lang('Verify Payment')</h2>
                <p>
                    @lang('To ensure the payment completed, your application must verify the payment state. You will need to make request with these following API end points.')
                </p>
                <p>
                    <x-badge text="Post" type="success"/>
                    <i>verify/payment</i>
                </p>
                <hr/>
                <h5 class="mb-4">@lang('Body Params')</h5>
                <div class="row">
                    <div class="col-12">
                        <p>
                            <b>order_id<span class="text-danger">*</span></b>
                            <x-badge text="string" />
                        </p>
                    </div>
                    <div class="col-md-8">
                        <p class="mb-0">@lang('This is unique id what you get response.')</p>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="562589636985"/>
                    </div>
                </div>
                <hr/>
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="alert alert-soft-info text-info" role="alert">
                            @lang('Note: status are pending, success, failed')
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-6 mt-5">
                <div class="card mb-2">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <ul class="nav nav-tabs" id="myTab2" role="tablist">
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    id="home2-tab2"
                                    data-bs-toggle="tab"
                                    href="#node2-js"
                                    role="tab"
                                    aria-controls="home"
                                    aria-selected="true">@lang('cURL')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="profile2-tab2"
                                    data-bs-toggle="tab"
                                    href="#php2"
                                    role="tab"
                                    aria-controls="profile"
                                    aria-selected="false">@lang('PHP')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="contact2-tab2"
                                    data-bs-toggle="tab"
                                    href="#ruby2"
                                    role="tab"
                                    aria-controls="contact"
                                    aria-selected="false">@lang('RUBY')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="node2-tab2"
                                    data-bs-toggle="tab"
                                    href="#node2"
                                    role="tab"
                                    aria-controls="contact"
                                    aria-selected="false">@lang('NODE JS')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="python2-tab2"
                                    data-bs-toggle="tab"
                                    href="#python2"
                                    role="tab"
                                    aria-controls="contact"
                                    aria-selected="false">@lang('PYTHON')</a>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-icon btn-primary ml-auto copy-btn">
                            <i class="bi-clipboard"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="tab-content tab-bordered" id="myTab3Content">
                            <div
                                class="tab-pane fade show active"
                                id="node2-js"
                                role="tabpanel"
                                aria-labelledby="home2-tab2"
                            >
                                       <pre>
<code id="copycurl2">
curl --location --request POST 'https://bugfinder.net/api/payment/verify' \
--header 'ApiKey: 9a5cd8e5beb32c70400bfdcf5f576562bd09202d' \
--header 'SecretKey: f2cc7f986a81b14525807a37a6a89ec99b8a82b6' \
--form 'order_id="1823456781"'
</code>
                                    </pre>
                            </div>
                            <div class="tab-pane fade" id="php2" role="tabpanel"
                                 aria-labelledby="profile2-tab2">
                                       <pre>
<code id="copyphp2">
?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://bugfinder.net/api/payment/verify',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('order_id' => '1823456781'),
  CURLOPT_HTTPHEADER => array(
    'ApiKey: 9a5cd8e5beb32c70400bfdcf5f576562bd09202d',
    'SecretKey: f2cc7f986a81b14525807a37a6a89ec99b8a82b6'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;


</code>

</pre>
                            </div>
                            <div class="tab-pane fade" id="ruby2" role="tabpanel" aria-labelledby="node2-tab2">
                                       <pre>
<code id="copyruby2">
require "uri"
require "net/http"

url = URI("https://bugfinder.net/api/payment/verify")

http = Net::HTTP.new(url.host, url.port);
request = Net::HTTP::Post.new(url)
request["ApiKey"] = "9a5cd8e5beb32c70400bfdcf5f576562bd09202d"
request["SecretKey"] = "f2cc7f986a81b14525807a37a6a89ec99b8a82b6"
form_data = [['order_id', '1823456781']]
request.set_form form_data, 'multipart/form-data'
response = http.request(request)
puts response.read_body

</code>

</pre>
                            </div>
                            <div class="tab-pane fade" id="node2" role="tabpanel"
                                 aria-labelledby="contact2-tab3">
                                       <pre>
<code id="copynode2">
var request = require('request');
var options = {
  'method': 'POST',
  'url': 'https://bugfinder.net/api/payment/verify',
  'headers': {
    'ApiKey': '9a5cd8e5beb32c70400bfdcf5f576562bd09202d',
    'SecretKey': 'f2cc7f986a81b14525807a37a6a89ec99b8a82b6'
  },
  formData: {
    'order_id': '1823456781'
  }
};
request(options, function (error, response) {
  if (error) throw new Error(error);
  console.log(response.body);
});


</code>

</pre>
                            </div>
                            <div class="tab-pane fade" id="python2" role="tabpanel"
                                 aria-labelledby="python2-tab3">
                                       <pre>
<code id="copypython2">
import requests

url = "https://bugfinder.net/api/payment/verify"

payload={'order_id': '1823456781'}
files=[

]
headers = {
  'ApiKey': '9a5cd8e5beb32c70400bfdcf5f576562bd09202d',
  'SecretKey': 'f2cc7f986a81b14525807a37a6a89ec99b8a82b6'
}

response = requests.request("POST", url, headers=headers, data=payload, files=files)

print(response.text)


</code>

</pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-header d-flex justify-content-between align-items-center">

                        <ul class="nav nav-tabs" id="myTab3" role="tablist">
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    id="home2-tab2"
                                    data-bs-toggle="tab"
                                    href="#success-code2"
                                    role="tab"
                                    aria-controls="home"
                                    aria-selected="true">@lang('200 OK')</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    id="profile2-tab2"
                                    data-bs-toggle="tab"
                                    href="#bad-request2"
                                    role="tab"
                                    aria-controls="profile"
                                    aria-selected="false">@lang('400 Bad Request')</a>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-icon btn-primary ml-auto copy-btn">
                            <i class="bi-clipboard"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="tab-content tab-bordered" id="myTab3Content">
                            <div
                                class="tab-pane fade show active"
                                id="success-code2"
                                role="tabpanel"
                                aria-labelledby="home2-tab2"
                            >
                                       <pre>
<code id="success2">
{
    "status": "success",
    "data": {
        "order_id": 1823456781,
        "status": "success"
    }
}
</code>
                                    </pre>
                            </div>
                            <div
                                class="tab-pane fade"
                                id="bad-request2"
                                role="tabpanel"
                                aria-labelledby="profile2-tab2"
                            >
										<pre><code>{
    "status": "error",
    "error": [
        "The order id field is required.",
        "The api key field is required.",
        "The secret key field is required."
    ]
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection


@push('script')
    <script src="{{ asset('assets/user/js/highlight.min.js') }}"></script>
    <script>
        'use strict'
        hljs.highlightAll();

        $(document).on('click', '.copy-btn', function () {
            var node = $(this).parents('.card-header').siblings('.card-body').find('.active').find('code').attr('id');

            var r = document.createRange();
            r.selectNode(document.getElementById(node));
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(r);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            Notiflix.Notify.success("Copied");
        })
    </script>
    @if ($errors->any())
        @php
            $collection = collect($errors->all());
            $errors = $collection->unique();
        @endphp
        <script>
            "use strict";
            @foreach ($errors as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush
