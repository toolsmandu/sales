@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';

    $stockChildren = [];

    if ($isAdmin) {
        $stockChildren[] = [
            'label' => '+ Add Keys',
            'route' => 'stock.keys.create',
            'active' => ['stock.keys.create'],
        ];
        $stockChildren[] = [
            'label' => 'All Keys',
            'route' => 'stock.index',
            'active' => ['stock.index'],
        ];
    }

    $chatbotChildren = [
        [
            'label' => 'Start Chat',
            'route' => 'chatbot.start',
            'active' => ['chatbot.start'],
        ],
        [
            'label' => 'Knowledgebase',
            'route' => 'chatbot.knowledgebase',
            'active' => ['chatbot.knowledgebase'],
        ],
    ];

    if ($isAdmin) {
        $chatbotChildren[] = [
            'label' => 'Add Knowledge',
            'route' => 'chatbot.knowledge',
            'active' => ['chatbot.knowledge'],
        ];
    }

    $links = [];

    $links[] = [
        'route' => 'products.index',
        'active' => ['products.index'],
        'label' => 'Products',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M192 64c0-17.7 14.3-32 32-32l64 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-8 0 0 64 120 0c39.8 0 72 32.2 72 72l0 56 8 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l8 0 0-56c0-13.3-10.7-24-24-24l-120 0 0 80 8 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l8 0 0-80-120 0c-13.3 0-24 10.7-24 24l0 56 8 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l8 0 0-56c0-39.8 32.2-72 72-72l120 0 0-64-8 0c-17.7 0-32-14.3-32-32l0-64z"/></svg>',
    ];

    $links[] = [
        'route' => 'orders.index',
        'active' => ['orders.index'],
        'label' => 'All Orders',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320 48C306.7 48 296 58.7 296 72L296 84L294.2 84C257.6 84 228 113.7 228 150.2C228 183.6 252.9 211.8 286 215.9L347 223.5C352.1 224.1 356 228.5 356 233.7C356 239.4 351.4 243.9 345.8 243.9L272 244C256.5 244 244 256.5 244 272C244 287.5 256.5 300 272 300L296 300L296 312C296 325.3 306.7 336 320 336C333.3 336 344 325.3 344 312L344 300L345.8 300C382.4 300 412 270.3 412 233.8C412 200.4 387.1 172.2 354 168.1L293 160.5C287.9 159.9 284 155.5 284 150.3C284 144.6 288.6 140.1 294.2 140.1L360 140C375.5 140 388 127.5 388 112C388 96.5 375.5 84 360 84L344 84L344 72C344 58.7 333.3 48 320 48zM141.3 405.5L98.7 448L64 448C46.3 448 32 462.3 32 480L32 544C32 561.7 46.3 576 64 576L384.5 576C413.5 576 441.8 566.7 465.2 549.5L591.8 456.2C609.6 443.1 613.4 418.1 600.3 400.3C587.2 382.5 562.2 378.7 544.4 391.8L424.6 480L312 480C298.7 480 288 469.3 288 456C288 442.7 298.7 432 312 432L384 432C401.7 432 416 417.7 416 400C416 382.3 401.7 368 384 368L231.8 368C197.9 368 165.3 381.5 141.3 405.5z"/></svg>',
    ];
    $links[] = [
        'route' => 'orders.expired',
        'active' => ['orders.expired'],
        'label' => 'Expired Orders',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm-8 120c0-13.3 10.7-24 24-24s24 10.7 24 24v120h80c13.3 0 24 10.7 24 24s-10.7 24-24 24H248c-13.3 0-24-10.7-24-24V128z"/></svg>',
    ];
    $links[] = [
        'route' => 'sheet.index',
        'active' => ['sheet.index'],
        'label' => 'Sheet',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H296c12.8 0 24.6-4 34.3-10.9L448 368V96c0-35.3-28.7-64-64-64H64zM384 135.5V320H320c-17.7 0-32 14.3-32 32v74.5H64c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16H384c8.8 0 16 7.2 16 16v39.5z"/></svg>',
    ];
    $links[] = [
        'route' => 'reports',
        'active' => ['reports'],
        'label' => 'Reports',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M64 64C28.7 64 0 92.7 0 128V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H64zM48 128c0-8.8 7.2-16 16-16H448c8.8 0 16 7.2 16 16V384c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V128zM120 192a24 24 0 1 0 0 48h16a24 24 0 1 0 0-48H120zm0 112a24 24 0 1 0 0 48h16a24 24 0 1 0 0-48H120zm96-88a24 24 0 0 1 24-24H408a24 24 0 1 1 0 48H240a24 24 0 0 1-24-24zm24 80a24 24 0 1 0 0 48H408a24 24 0 1 0 0-48H240z"/></svg>',
    ];
    $links[] = [
        'route' => 'qr.scan',
        'active' => ['qr.scan'],
        'label' => 'QR Scan',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M64 160l64 0 0-64-64 0 0 64zM0 80C0 53.5 21.5 32 48 32l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48L0 80zM64 416l64 0 0-64-64 0 0 64zM0 336c0-26.5 21.5-48 48-48l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48l0-96zM320 96l0 64 64 0 0-64-64 0zM304 32l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48l0-96c0-26.5 21.5-48 48-48zM288 352a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm0 64c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm96 32c0-17.7 14.3-32 32-32s32 14.3 32 32-14.3 32-32 32-32-14.3-32-32zm32-96a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm-32 32a32 32 0 1 1 -64 0 32 32 0 1 1 64 0z"/></svg>',
    ];
    $links[] = [
        'route' => 'stock.index',
        'active' => ['stock.index', 'stock.keys.*'],
        'label' => 'Stock Keys',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0 160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17l0 80c0 13.3 10.7 24 24 24l80 0c13.3 0 24-10.7 24-24l0-40 40 0c13.3 0 24-10.7 24-24l0-40 40 0c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z"/></svg>',
        'children' => $stockChildren,
    ];
    $links[] = [
        'route' => $isAdmin ? 'chatbot.knowledge' : 'chatbot.start',
        'active' => ['chatbot.knowledge', 'chatbot.knowledgebase', 'chatbot.start'],
        'label' => 'Chatbot',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M384 144c0 97.2-86 176-192 176-26.7 0-52.1-5-75.2-14L35.2 349.2c-9.3 4.9-20.7 3.2-28.2-4.2s-9.2-18.9-4.2-28.2l35.6-67.2C14.3 220.2 0 183.6 0 144 0 46.8 86-32 192-32S384 46.8 384 144zm0 368c-94.1 0-172.4-62.1-188.8-144 120-1.5 224.3-86.9 235.8-202.7 83.3 19.2 145 88.3 145 170.7 0 39.6-14.3 76.2-38.4 105.6l35.6 67.2c4.9 9.3 3.2 20.7-4.2 28.2s-18.9 9.2-28.2 4.2L459.2 498c-23.1 9-48.5 14-75.2 14z"/></svg>',
        'children' => $chatbotChildren,
    ];

    if ($isAdmin) {
        $links[] = [
            'route' => 'payments.index',
            'active' => ['payments.index', 'payments.balance', 'payments.statements', 'payments.manage', 'payments.withdraw'],
            'label' => 'Billing',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M64 32C28.7 32 0 60.7 0 96L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-192c0-35.3-28.7-64-64-64L72 128c-13.3 0-24-10.7-24-24S58.7 80 72 80l384 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L64 32zM416 256a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>',
            'children' => [
                [
                    'label' => 'Statements',
                    'route' => 'payments.statements',
                    'active' => ['payments.statements'],
                ],
                [
                    'label' => 'Withdraw Funds',
                    'route' => 'payments.withdraw',
                    'active' => ['payments.withdraw'],
                ],
                [
                    'label' => 'Payment Methods',
                    'route' => 'payments.manage',
                    'active' => ['payments.manage'],
                ],
            ],
        ];

    }

    $links[] = [
        'route' => 'coupons.index',
        'active' => ['coupons.index'],
        'label' => 'Coupons',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M192 128a96 96 0 1 0 -192 0 96 96 0 1 0 192 0zM448 384a96 96 0 1 0 -192 0 96 96 0 1 0 192 0zM438.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-384 384c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l384-384z"/></svg>',
    ];

    $links[] = [
        'route' => 'user-logs.attendance',
        'active' => ['user-logs.attendance'],
        'label' => 'Attendance Logs',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M64 64c-17.7 0-32 14.3-32 32l0 320c0 17.7 14.3 32 32 32l384 0c17.7 0 32-14.3 32-32l0-320c0-17.7-14.3-32-32-32L64 64zm0 32l384 0 0 320-384 0 0-320zm48 48c-17.7 0-32 14.3-32 32s14.3 32 32 32 32-14.3 32-32-14.3-32-32-32zm0 128c-17.7 0-32 14.3-32 32s14.3 32 32 32 32-14.3 32-32-14.3-32-32-32zm0 128c-17.7 0-32 14.3-32 32s14.3 32 32 32 32-14.3 32-32-14.3-32-32-32zm96-192l208 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-208 0c-8.8 0-16 7.2-16 16s7.2 16 16 16zm0 128l208 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-208 0c-8.8 0-16 7.2-16 16s7.2 16 16 16zm0 128l208 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-208 0c-8.8 0-16 7.2-16 16s7.2 16 16 16z"/></svg>',
    ];

    $links[] = [
        'route' => 'user-logs.tasks',
        'active' => ['user-logs.tasks'],
        'label' => 'Tasks',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm0 48H384c8.8 0 16 7.2 16 16V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16zM96 168c0-13.3 10.7-24 24-24H216c13.3 0 24 10.7 24 24s-10.7 24-24 24H120c-13.3 0-24-10.7-24-24zm0 96c0-13.3 10.7-24 24-24H312c13.3 0 24 10.7 24 24s-10.7 24-24 24H120c-13.3 0-24-10.7-24-24zm0 96c0-13.3 10.7-24 24-24H280c13.3 0 24 10.7 24 24s-10.7 24-24 24H120c-13.3 0-24-10.7-24-24zm256-192c-13.3 0-24-10.7-24-24s10.7-24 24-24 24 10.7 24 24-10.7 24-24 24zm0 96c-13.3 0-24-10.7-24-24s10.7-24 24-24 24 10.7 24 24-10.7 24-24 24zm0 96c-13.3 0-24-10.7-24-24s10.7-24 24-24 24 10.7 24 24-10.7 24-24 24z"/></svg>',
    ];
@endphp

<aside class="dashboard-nav" data-dashboard-nav>
    <button type="button" class="dashboard-nav__mobile-trigger" data-nav-toggle>
        <span>Menu</span>
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>
    </button>
    <div class="dashboard-nav__content">
        <div class="dashboard-nav__heading">
            <span>Quick Access</span>
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M4 5h16M4 12h16M4 19h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </div>
        <ul>
        @foreach ($links as $link)
            @php
                $isActive = request()->routeIs(...$link['active']);
                $hasChildren = !empty($link['children']);
                $childActive = false;
                if ($hasChildren) {
                    foreach ($link['children'] as $child) {
                        if (!empty($child['active']) && request()->routeIs(...$child['active'])) {
                            $childActive = true;
                            break;
                        }
                    }
                }
                $isExpanded = $isActive || $childActive;
                $linkUrl = '#';
                if (!empty($link['route']) && \Illuminate\Support\Facades\Route::has($link['route'])) {
                    $linkUrl = route($link['route']);
                } elseif (!empty($link['url'])) {
                    $linkUrl = $link['url'];
                } elseif (!empty($link['route'])) {
                    $linkUrl = url($link['route']);
                }
            @endphp
            <li class="{{ $isExpanded ? 'is-expanded' : '' }}">
                @if ($hasChildren)
                    <button
                        type="button"
                        class="dashboard-nav__accordion {{ ($isActive || $childActive) ? 'is-active' : '' }}"
                        data-accordion-toggle
                        aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
                        data-target="submenu-{{ \Illuminate\Support\Str::slug($link['label']) }}"
                    >
                        <span>
                            <span class="dashboard-nav__icon" aria-hidden="true">{!! $link['icon'] !!}</span>
                            {{ $link['label'] }}
                        </span>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                @else
                    <a
                        href="{{ $linkUrl }}"
                        class="{{ $isActive ? 'is-active' : '' }}"
                        aria-current="{{ $isActive ? 'page' : 'false' }}"
                    >
                        <span>
                            <span class="dashboard-nav__icon" aria-hidden="true">{!! $link['icon'] !!}</span>
                            {{ $link['label'] }}
                        </span>
                    </a>
                @endif

                @if ($hasChildren)
                    <ul id="submenu-{{ \Illuminate\Support\Str::slug($link['label']) }}" class="dashboard-nav__sublist" aria-hidden="{{ $isExpanded ? 'false' : 'true' }}">
                        @foreach ($link['children'] as $child)
                            @php
                                $childIsActive = !empty($child['active']) && request()->routeIs(...$child['active']);
                            @endphp
                            <li>
                                <a
                                    href="{{ route($child['route']) }}"
                                    class="dashboard-nav__sublink {{ $childIsActive ? 'is-active' : '' }}"
                                >
                                    {{ $child['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
        </ul>
    </div>
</aside>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dashboardNav = document.querySelector('[data-dashboard-nav]');
                const navToggle = dashboardNav?.querySelector('[data-nav-toggle]');
                const navLinks = dashboardNav?.querySelectorAll('a');

                const closeNavOnMobile = () => {
                    if (dashboardNav && window.matchMedia('(min-width: 1024px)').matches) {
                        dashboardNav.classList.remove('is-open');
                    }
                };

                if (navToggle && dashboardNav) {
                    navToggle.addEventListener('click', function () {
                        dashboardNav.classList.toggle('is-open');
                    });
                }

                if (navLinks) {
                    navLinks.forEach((link) => {
                        link.addEventListener('click', () => {
                            if (!window.matchMedia('(min-width: 1024px)').matches) {
                                dashboardNav?.classList.remove('is-open');
                            }
                        });
                    });
                }

                window.addEventListener('resize', closeNavOnMobile);

                document.querySelectorAll('[data-accordion-toggle]').forEach(function (button) {
                    var listItem = button.closest('li');
                    var targetId = button.getAttribute('data-target');
                    var sublist = targetId ? document.getElementById(targetId) : (listItem ? listItem.querySelector('.dashboard-nav__sublist') : null);
                    if (!listItem || !sublist) {
                        return;
                    }

                    var setExpanded = function (expanded) {
                        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                        sublist.setAttribute('aria-hidden', expanded ? 'false' : 'true');
                        listItem.classList.toggle('is-expanded', expanded);
                    };

                    button.addEventListener('click', function () {
                        var shouldExpand = !listItem.classList.contains('is-expanded');
                        if (shouldExpand) {
                            document.querySelectorAll('[data-accordion-toggle]').forEach(function (other) {
                                if (other === button) {
                                    return;
                                }
                                var otherItem = other.closest('li');
                                var otherTargetId = other.getAttribute('data-target');
                                var otherSublist = otherTargetId ? document.getElementById(otherTargetId) : (otherItem ? otherItem.querySelector('.dashboard-nav__sublist') : null);
                                if (otherItem && otherSublist) {
                                    other.setAttribute('aria-expanded', 'false');
                                    otherSublist.setAttribute('aria-hidden', 'true');
                                    otherItem.classList.remove('is-expanded');
                                }
                            });
                        }
                        setExpanded(shouldExpand);
                    });

                    if (listItem.classList.contains('is-expanded')) {
                        setExpanded(true);
                    } else {
                        setExpanded(false);
                    }
                });
            });
        </script>
    @endpush
@endonce
