<section class="panel" style="margin-bottom:20px">
    <h2 style="margin-top:0">আপনার পণ্যসমূহ</h2>
    <p>অর্ডার সেকশনের পণ্যের নাম, ছবি, দাম, ব্যাজ ও প্রদর্শন ক্রম এখান থেকে পরিচালনা করুন। সক্রিয় পণ্যগুলো ওয়েবসাইটে দেখাবে।</p>
    <p><a href="{{ route('admin.landing.edit', 'order') }}#order-success-settings">অর্ডার সফল হওয়ার পপআপের লেখা ও ছবি পরিবর্তন করুন ↓</a></p>
    <div style="display:flex;flex-wrap:wrap;gap:16px">
        <a href="{{ route('admin.products.create') }}"><i class="fa-solid fa-plus"></i> নতুন পণ্য যোগ করুন</a>
        <a href="{{ route('admin.products.index') }}"><i class="fa-solid fa-list"></i> পণ্য দেখুন / এডিট করুন</a>
    </div>
</section>
