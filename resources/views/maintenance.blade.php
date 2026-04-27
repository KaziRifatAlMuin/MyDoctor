<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>সিস্টেম রক্ষণাবেক্ষণ - মাইডক্টর | System Maintenance - MyDoctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        .pulse-ring { animation: pulse-ring 2s infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float { animation: float 3s ease-in-out infinite; }
        .maintenance-container {
            max-width: 88rem;
        }
        @media (min-width: 1536px) {
            .maintenance-container {
                max-width: 90rem;
            }
        }
        .lang-btn {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-8 lg:px-12 py-12">
        <div class="maintenance-container w-full mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transition-all duration-300">
                <div class="relative bg-gradient-to-r from-blue-600 to-blue-700 px-6 sm:px-8 pt-6 pb-0 flex justify-end">
                    <div class="inline-flex bg-white/20 backdrop-blur-sm rounded-full p-1 gap-1 shadow-sm">
                        <button id="btn-en" class="lang-btn px-4 py-1.5 text-sm font-medium rounded-full transition text-white bg-blue-800/40 hover:bg-blue-700">English</button>
                        <button id="btn-bn" class="lang-btn px-4 py-1.5 text-sm font-medium rounded-full transition text-white hover:bg-blue-800/40">বাংলা</button>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 sm:px-10 py-8 sm:py-12">
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="pulse-ring absolute inset-0 rounded-full"></div>
                            <div class="relative w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-xl">
                                <svg class="w-10 h-10 text-blue-600 float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <h1 id="main-title-en" class="text-3xl sm:text-5xl font-bold text-white text-center mb-2">System Maintenance & Repairs running</h1>
                    <h1 id="main-title-bn" class="text-3xl sm:text-5xl font-bold text-white text-center mb-2 hidden">সিস্টেম রক্ষণাবেক্ষণ ও মেরামত চলছে</h1>
                    <p id="sub-en" class="text-blue-100 text-center text-3xl sm:text-5xl">Sorry for temporary inconvenience</p>
                    <p id="sub-bn" class="text-blue-100 text-center text-3xl sm:text-5xl hidden">সাময়িক অসুবিধার জন্য দুঃখিত </p>
                </div>
                <div class="px-6 sm:px-10 py-8 sm:py-10">
                    <div class="mb-10">
                        <p id="desc-en" class="text-gray-700 text-center text-xl leading-relaxed font-medium">
                            We're currently performing essential system maintenance and repairs to improve your experience. We appreciate your patience!
                        </p>
                        <p id="desc-bn" class="text-gray-700 text-center text-xl leading-relaxed font-medium hidden">
                            আমরা আপনার অভিজ্ঞতা উন্নত করতে প্রয়োজনীয় সিস্টেম রক্ষণাবেক্ষণ এবং মেরামত করছি। আপনার ধৈর্যের জন্য ধন্যবাদ!
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 id="status-head-en" class="text-md font-semibold text-blue-900">Current Status</h3>
                                    <h3 id="status-head-bn" class="text-md font-semibold text-blue-900 hidden">বর্তমান অবস্থা</h3>
                                    <p id="status-val-en" class="mt-1 text-sm text-blue-700">Maintenance in progress</p>
                                    <p id="status-val-bn" class="mt-1 text-sm text-blue-700 hidden">রক্ষণাবেক্ষণ চলছে</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00-.293.707l-2.828 2.829a1 1 0 101.414 1.414L8 9.414V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 id="time-head-en" class="text-md font-semibold text-amber-900">Estimated Time</h3>
                                    <h3 id="time-head-bn" class="text-md font-semibold text-amber-900 hidden">আনুমানিক সময়</h3>
                                    <p id="time-val-en" class="mt-1 text-sm text-amber-700">Expected to be complete soon</p>
                                    <p id="time-val-bn" class="mt-1 text-sm text-amber-700 hidden">শীঘ্রই সম্পন্ন হবে বলে আশা করা হচ্ছে</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 3.062v6.372a3.066 3.066 0 01-2.812 3.062p-3.28 0-6.116 1.584a3.066 3.066 0 01-3.276 0 3.066 3.066 0 01-3.28-1.584V6.517a3.066 3.066 0 012.812-3.062zm9.068.95a1.043 1.043 0 00-.86.505l-2.287 3.998a.993.993 0 01-.84.505H8.217a.993.993 0 00-.84.505l-2.287-3.998a1.042 1.042 0 00-.86-.505 1.023 1.023 0 00-1.006 1.042v3.356a1.09 1.09 0 001.004 1.042h.952a.997.997 0 01.21 1.971H5.226a2.991 2.991 0 01-2.992-2.992V6.283a2.99 2.99 0 012.992-2.992h2.4c.696-1.577 2.158-2.647 3.97-2.647.424 0 .826.218 1.023.623l1.223 2.13c.21.368.524.620.906.62h.952a1.091 1.091 0 011.004 1.042v3.356a1.09 1.09 0 01-1.004 1.042h-.952a.997.997 0 01-.21-1.971h.952a1.023 1.023 0 001.006-1.042V6.283a1.023 1.023 0 00-1.006-1.042z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 id="work-head-en" class="text-md font-semibold text-green-900">What We're Doing</h3>
                                    <h3 id="work-head-bn" class="text-md font-semibold text-green-900 hidden">আমরা যা করছি</h3>
                                    <p id="work-val-en" class="mt-1 text-sm text-green-700">System repairs and improvements</p>
                                    <p id="work-val-bn" class="mt-1 text-sm text-green-700 hidden">সিস্টেম মেরামত এবং উন্নয়ন</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center mb-10">
                        <div class="flex space-x-3">
                            <div class="w-2.5 h-2.5 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                            <div class="w-2.5 h-2.5 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.15s;"></div>
                            <div class="w-2.5 h-2.5 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.3s;"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-5 mb-10 border border-gray-100">
                        <p id="safe-en" class="text-base text-gray-700 text-center">
                            <strong>No data will be lost.</strong> All your health records, medicine schedules, and reminders are safe and secure. We'll be back online shortly.
                        </p>
                        <p id="safe-bn" class="text-base text-gray-700 text-center hidden">
                            <strong>কোনো তথ্য হারাবে না।</strong> আপনার সকল স্বাস্থ্য রেকর্ড, ঔষধের সময়সূচী এবং রিমাইন্ডারগুলো সম্পূর্ণ নিরাপদ। আমরা খুব শীঘ্রই অনলাইনে ফিরব।
                        </p>
                    </div>
                    <div class="text-center border-t border-gray-200 pt-8">
                        <p id="contact-q-en" class="text-gray-600 text-sm mb-4">Have questions? Contact us:</p>
                        <p id="contact-q-bn" class="text-gray-600 text-sm mb-4 hidden">প্রশ্ন থাকলে？ যোগাযোগ করুন:</p>
                        <div class="flex flex-col sm:flex-row justify-center gap-6 flex-wrap">
                            <div>
                                <strong class="text-gray-700">Email:</strong>
                                <a href="mailto:support@mydoctor.com" class="text-blue-600 hover:text-blue-800 font-medium ml-1">support@mydoctor.com</a>
                            </div>
                            <div>
                                <strong class="text-gray-700">Phone:</strong>
                                <a href="tel:+8809608050000" class="text-blue-600 hover:text-blue-800 font-medium ml-1">+88 0960-8050-000</a>
                            </div>
                        </div>
                        <p class="text-gray-400 text-xs mt-5" id="update-time-display">Last check: --:-- UTC</p>
                    </div>
                </div>
            </div>
            <div class="mt-10 text-center">
                <p class="text-gray-600 text-sm flex items-center justify-center gap-1 flex-wrap">
                    <svg class="inline-block w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <span id="footer-text-en">MyDoctor - Your Health Companion</span>
                    <span id="footer-text-bn" class="hidden">মাইডক্টর - আপনার স্বাস্থ্যসাথী</span>
                </p>
            </div>
        </div>
    </div>
    <script>
        const enButton = document.getElementById('btn-en');
        const bnButton = document.getElementById('btn-bn');
        const enIds = [
            'main-title-en', 'sub-en', 'desc-en', 'status-head-en', 'status-val-en',
            'time-head-en', 'time-val-en', 'work-head-en', 'work-val-en', 'safe-en',
            'contact-q-en', 'footer-text-en'
        ];
        const bnIds = [
            'main-title-bn', 'sub-bn', 'desc-bn', 'status-head-bn', 'status-val-bn',
            'time-head-bn', 'time-val-bn', 'work-head-bn', 'work-val-bn', 'safe-bn',
            'contact-q-bn', 'footer-text-bn'
        ];
        function setLanguage(lang) {
            if (lang === 'en') {
                bnIds.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.add('hidden');
                });
                enIds.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.remove('hidden');
                });
                enButton.classList.add('bg-blue-800/60', 'text-white');
                enButton.classList.remove('bg-transparent', 'hover:bg-blue-800/40');
                bnButton.classList.remove('bg-blue-800/60');
                bnButton.classList.add('bg-transparent', 'hover:bg-blue-800/40');
                localStorage.setItem('mydoctor_lang', 'en');
            } else {
                enIds.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.add('hidden');
                });
                bnIds.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.remove('hidden');
                });
                bnButton.classList.add('bg-blue-800/60', 'text-white');
                bnButton.classList.remove('bg-transparent', 'hover:bg-blue-800/40');
                enButton.classList.remove('bg-blue-800/60');
                enButton.classList.add('bg-transparent', 'hover:bg-blue-800/40');
                localStorage.setItem('mydoctor_lang', 'bn');
            }
        }
        enButton.addEventListener('click', () => setLanguage('en'));
        bnButton.addEventListener('click', () => setLanguage('bn'));
        const savedLang = localStorage.getItem('mydoctor_lang');
        if (savedLang === 'bn') {
            setLanguage('bn');
        } else {
            setLanguage('en');
        }
        function updateTimeDisplay() {
            const now = new Date();
            const hours = String(now.getUTCHours()).padStart(2, '0');
            const minutes = String(now.getUTCMinutes()).padStart(2, '0');
            const seconds = String(now.getUTCSeconds()).padStart(2, '0');
            const timeElement = document.getElementById('update-time-display');
            if (timeElement) {
                const langState = localStorage.getItem('mydoctor_lang');
                if (langState === 'bn') {
                    timeElement.textContent = `শেষ রিফ্রেশ: ${hours}:${minutes}:${seconds} UTC`;
                } else {
                    timeElement.textContent = `Last refresh: ${hours}:${minutes}:${seconds} UTC`;
                }
            }
        }
        setTimeout(function() {
            location.reload();
        }, 30000);
        updateTimeDisplay();
        setInterval(updateTimeDisplay, 1000);
    </script>
</body>
</html>