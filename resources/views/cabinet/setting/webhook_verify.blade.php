<link href="{{ config('cratos.urls.theme') }}css/webhook_verify.css" rel="stylesheet">

<h2>Verifying webhook sender</h2>
<p>
    In order to make sure that a webhook is sent by us, we have the possibility to sign it with the HMAC Sha1Hex algorithm.
    If you want to utilize this feature, set a Secret Key value for each webhook via settings.
    To verify that a webhook is sent by us
</p>

<p> 1. Get a webhook x-payload-digest header value and payload as it is without any alteration or converting to json.</p>
<h6>2. Convert the HTTP webhook body into bytes.</h6>
<p>3. Perform calculations on your side to create a digest using Secret Key, raw webhook payload in bytes and <code>HMAC Sha1Hex</code> algorithm.</p>
<p>4. Compare <code>x-payload-digest</code> header value with calculated digest.</p>

<br><br>
<h6> Example of computing digest </h6>
<div class="language-javascript extra-class" data-v-5e6d060f><pre class="language-javascript" data-v-5e6d060f><code data-v-5e6d060f><span class="token keyword" data-v-5e6d060f>export</span> <span class="token keyword" data-v-5e6d060f>function</span> <span class="token function" data-v-5e6d060f>checkDigest</span><span class="token punctuation" data-v-5e6d060f>(</span><span class="token parameter" data-v-5e6d060f>req</span><span class="token punctuation" data-v-5e6d060f>)</span><span class="token punctuation" data-v-5e6d060f>:</span> boolean <span class="token punctuation" data-v-5e6d060f>{</span>
 <span class="token keyword" data-v-5e6d060f>const</span> calculatedDigest <span class="token operator" data-v-5e6d060f>=</span> crypto
  <span class="token punctuation" data-v-5e6d060f>.</span><span class="token function" data-v-5e6d060f>createHmac</span><span class="token punctuation" data-v-5e6d060f>(</span><span class="token string" data-v-5e6d060f>'sha1'</span><span class="token punctuation" data-v-5e6d060f>,</span> <span class="token constant" data-v-5e6d060f>SUMSUB_PRIVATE_KEY</span><span class="token punctuation" data-v-5e6d060f>)</span>
  <span class="token punctuation" data-v-5e6d060f>.</span><span class="token function" data-v-5e6d060f>update</span><span class="token punctuation" data-v-5e6d060f>(</span>req<span class="token punctuation" data-v-5e6d060f>.</span>rawBody<span class="token punctuation" data-v-5e6d060f>)</span>
  <span class="token punctuation" data-v-5e6d060f>.</span><span class="token function" data-v-5e6d060f>digest</span><span class="token punctuation" data-v-5e6d060f>(</span><span class="token string" data-v-5e6d060f>'hex'</span><span class="token punctuation" data-v-5e6d060f>)</span>

 <span class="token keyword" data-v-5e6d060f>return</span> calculatedDigest <span class="token operator" data-v-5e6d060f>===</span> req<span class="token punctuation" data-v-5e6d060f>.</span>headers<span class="token punctuation" data-v-5e6d060f>[</span><span class="token string" data-v-5e6d060f>'x-payload-digest'</span><span class="token punctuation" data-v-5e6d060f>]</span>
<span class="token punctuation" data-v-5e6d060f>}</span>
</code></pre></div>
<br><br><br><br>
