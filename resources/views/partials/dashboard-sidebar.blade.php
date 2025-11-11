@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';

    $stockChildren = [
        [

                        'label' => 'Add Keys',
            'route' => 'stock.keys.create',
            'active' => ['stock.keys.create'],
        ],
    ];

    if ($isAdmin) {
        $stockChildren[] = [
            'label' => 'All Stock',
            'route' => 'stock.index',
            'active' => ['stock.index'],
        ];
    }

    $chatbotChildren = [
        [
            'label' => 'Chatbot',
            'route' => 'chatbot.simulator',
            'active' => ['chatbot.simulator'],
        ],
    ];

    if ($isAdmin) {
        $chatbotChildren[] = [
            'label' => 'Add Knowledge',
            'route' => 'chatbot.knowledge',
            'active' => ['chatbot.knowledge'],
        ];
        $chatbotChildren[] = [
            'label' => 'Knowledgebase',
            'route' => 'chatbot.existing',
            'active' => ['chatbot.existing'],
        ];
    }

    $links = [
        [
            'route' => 'sales.index',
            'active' => ['sales.index'],
            'label' => 'Sales Record',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M320 48C306.7 48 296 58.7 296 72L296 84L294.2 84C257.6 84 228 113.7 228 150.2C228 183.6 252.9 211.8 286 215.9L347 223.5C352.1 224.1 356 228.5 356 233.7C356 239.4 351.4 243.9 345.8 243.9L272 244C256.5 244 244 256.5 244 272C244 287.5 256.5 300 272 300L296 300L296 312C296 325.3 306.7 336 320 336C333.3 336 344 325.3 344 312L344 300L345.8 300C382.4 300 412 270.3 412 233.8C412 200.4 387.1 172.2 354 168.1L293 160.5C287.9 159.9 284 155.5 284 150.3C284 144.6 288.6 140.1 294.2 140.1L360 140C375.5 140 388 127.5 388 112C388 96.5 375.5 84 360 84L344 84L344 72C344 58.7 333.3 48 320 48zM141.3 405.5L98.7 448L64 448C46.3 448 32 462.3 32 480L32 544C32 561.7 46.3 576 64 576L384.5 576C413.5 576 441.8 566.7 465.2 549.5L591.8 456.2C609.6 443.1 613.4 418.1 600.3 400.3C587.2 382.5 562.2 378.7 544.4 391.8L424.6 480L312 480C298.7 480 288 469.3 288 456C288 442.7 298.7 432 312 432L384 432C401.7 432 416 417.7 416 400C416 382.3 401.7 368 384 368L231.8 368C197.9 368 165.3 381.5 141.3 405.5z"/></svg>',
        ],
        [
            'route' => 'stock.index',
            'active' => ['stock.index', 'stock.keys.*'],
            'label' => 'Stock Keys',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0 160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17l0 80c0 13.3 10.7 24 24 24l80 0c13.3 0 24-10.7 24-24l0-40 40 0c13.3 0 24-10.7 24-24l0-40 40 0c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z"/></svg>',
            'children' => $stockChildren,
        ],
        [
            'route' => $isAdmin ? 'chatbot.knowledge' : 'chatbot.simulator',
            'active' => ['chatbot.knowledge', 'chatbot.existing', 'chatbot.simulator'],
            'label' => 'Chatbot',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M384 144c0 97.2-86 176-192 176-26.7 0-52.1-5-75.2-14L35.2 349.2c-9.3 4.9-20.7 3.2-28.2-4.2s-9.2-18.9-4.2-28.2l35.6-67.2C14.3 220.2 0 183.6 0 144 0 46.8 86-32 192-32S384 46.8 384 144zm0 368c-94.1 0-172.4-62.1-188.8-144 120-1.5 224.3-86.9 235.8-202.7 83.3 19.2 145 88.3 145 170.7 0 39.6-14.3 76.2-38.4 105.6l35.6 67.2c4.9 9.3 3.2 20.7-4.2 28.2s-18.9 9.2-28.2 4.2L459.2 498c-23.1 9-48.5 14-75.2 14z"/></svg>',
            'children' => $chatbotChildren,
        ],
    ];

    if ($isAdmin) {
        $links[] = [
            'route' => 'products.index',
            'active' => ['products.index'],
            'label' => 'Products',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M192 64c0-17.7 14.3-32 32-32l64 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-8 0 0 64 120 0c39.8 0 72 32.2 72 72l0 56 8 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l8 0 0-56c0-13.3-10.7-24-24-24l-120 0 0 80 8 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l8 0 0-80-120 0c-13.3 0-24 10.7-24 24l0 56 8 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l8 0 0-56c0-39.8 32.2-72 72-72l120 0 0-64-8 0c-17.7 0-32-14.3-32-32l0-64z"/></svg>',
        ];

        $links[] = [
            'route' => 'coupons.index',
            'active' => ['coupons.index'],
            'label' => 'Coupon Code',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M192 128a96 96 0 1 0 -192 0 96 96 0 1 0 192 0zM448 384a96 96 0 1 0 -192 0 96 96 0 1 0 192 0zM438.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-384 384c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l384-384z"/></svg>',
        ];



        $links[] = [
            'route' => 'payments.index',
            'active' => ['payments.index', 'payments.balance', 'payments.statements', 'payments.manage', 'payments.withdraw'],
            'label' => 'Billing',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 32C28.7 32 0 60.7 0 96L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-192c0-35.3-28.7-64-64-64L72 128c-13.3 0-24-10.7-24-24S58.7 80 72 80l384 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L64 32zM416 256a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>',
            'children' => [
                [
                    'label' => 'Current Balance',
                    'route' => 'payments.balance',
                    'active' => ['payments.balance'],
                ],
                [
                    'label' => 'Statements',
                    'route' => 'payments.statements',
                    'active' => ['payments.statements'],
                ],
                [
                    'label' => 'Payment Methods',
                    'route' => 'payments.manage',
                    'active' => ['payments.manage'],
                ],
                [
                    'label' => 'Withdraw Funds',
                    'route' => 'payments.withdraw',
                    'active' => ['payments.withdraw'],
                ],
            ],
        ];

        $links[] = [
            'route' => 'qr.scan',
            'active' => ['qr.scan'],
            'label' => 'QR Scan',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 160l64 0 0-64-64 0 0 64zM0 80C0 53.5 21.5 32 48 32l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48L0 80zM64 416l64 0 0-64-64 0 0 64zM0 336c0-26.5 21.5-48 48-48l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48l0-96zM320 96l0 64 64 0 0-64-64 0zM304 32l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48l0-96c0-26.5 21.5-48 48-48zM288 352a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm0 64c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm96 32c0-17.7 14.3-32 32-32s32 14.3 32 32-14.3 32-32 32-32-14.3-32-32zm32-96a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm-32 32a32 32 0 1 1 -64 0 32 32 0 1 1 64 0z"/></svg>',
        ];
    }

@endphp

<aside class="dashboard-nav">
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
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $link['icon'] !!}</svg>
                            {{ $link['label'] }}
                        </span>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                @else
                    <a
                        href="{{ route($link['route']) }}"
                        class="{{ $isActive ? 'is-active' : '' }}"
                        aria-current="{{ $isActive ? 'page' : 'false' }}"
                    >
                        <span>
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $link['icon'] !!}</svg>
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

        
  
</aside>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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
