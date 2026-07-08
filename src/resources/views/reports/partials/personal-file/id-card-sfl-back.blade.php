@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
@endphp

<div class="sfl-card-side">

    {{-- Header decoration (colors swapped) --}}
    <div class="sfl-decor">
        <div class="sfl-decor-shape is-navy sfl-decor-bh-gold"></div>
        <div class="sfl-decor-shape is-gold sfl-decor-bh-navy"></div>
    </div>

    {{-- Logo row --}}
    <div class="sfl-logo-area" style="padding: 0.5mm 1.5mm;">
        <div class="sfl-logo-text">
            <img src="{{ asset(general()->logo()) }}" alt="logo" class="sfl-logo-img">
        </div>
    </div>

    {{-- Back content --}}
    <div class="sfl-back-content">

        <div class="sfl-ribbon">{{ $t('শর্তাবলী', 'Terms & Conditions') }}</div>

        <ul class="sfl-terms">
            <li>১. {{ $t('এই কার্ড হস্তান্তর যোগ্য নহে।', 'This card is non-transferable.') }}</li>
            <li>২. {{ $t('এই কার্ড হারানো বা নষ্ট হলে কর্তৃপক্ষকে অবহিত করতে হবে এবং ৩০০ টাকা জরিমানা দিয়ে পুনরায় সংগ্রহ করতে হবে।', 'If lost or damaged, inform authority and pay BDT 300 for replacement.') }}</li>
            <li>৩. {{ $t('চাকরি ত্যাগের পূর্বে অবশ্যই কার্ডটি ফেরত দিতে হবে।', 'Return the card before leaving service.') }}</li>
        </ul>

        <div class="sfl-squares">
            @for($i = 0; $i < 7; $i++)<div class="sfl-sq"></div>@endfor
        </div>

        <p class="sfl-found-msg">
            {{ $t('কার্ডটি কোথাও পাওয়া গেলে নিম্নোক্ত ঠিকানায় পৌঁছে দেওয়ার জন্য অনুরোধ করা হলো।', 'If found, please return to the address below.') }}
        </p>

        <address class="sfl-contact">
            <div class="sfl-contact-item">
                <span class="sfl-contact-icon"><i class="fa fa-map-marker-alt"></i></span>
                <span>Kathgora, Ashulia, Zirabo, Savar, Dhaka, Bangladesh.</span>
            </div>
            <div class="sfl-contact-item">
                <span class="sfl-contact-icon"><i class="fa fa-phone"></i></span>
                <span>+880 1797-642195</span>
            </div>
            <div class="sfl-contact-item">
                <span class="sfl-contact-icon"><i class="fa fa-envelope"></i></span>
                <span>info@suhanafashions.com</span>
            </div>
            <div class="sfl-contact-item">
                <span class="sfl-contact-icon"><i class="fab fa-facebook"></i></span>
                <span>www.facebook.com/Suhana.FL/</span>
            </div>
            <div class="sfl-contact-item">
                <span class="sfl-contact-icon"><i class="fa fa-globe"></i></span>
                <span>www.suhanafashions.com</span>
            </div>
        </address>

    </div>

    {{-- Footer decoration (colors swapped) --}}
    <div class="sfl-decor">
        <div class="sfl-decor-shape is-navy sfl-decor-bf-gold"></div>
        <div class="sfl-decor-shape is-gold sfl-decor-bf-navy"></div>
    </div>

</div>
