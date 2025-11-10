@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';

    $stockChildren = [
        [
            'label' => 'All Stock',
            'route' => 'stock.index',
            'active' => ['stock.index'],
        ],
    ];

    if ($isAdmin) {
        $stockChildren[] = [
            'label' => 'Add Keys',
            'route' => 'stock.keys.create',
            'active' => ['stock.keys.create'],
        ];
    }

    $chatbotChildren = [
        [
            'label' => 'Chatbot Simulator',
            'route' => 'chatbot.simulator',
            'active' => ['chatbot.simulator'],
        ],
    ];

    if ($isAdmin) {
        $chatbotChildren[] = [
            'label' => 'Chatbot Knowledge Base',
            'route' => 'chatbot.knowledge',
            'active' => ['chatbot.knowledge'],
        ];
        $chatbotChildren[] = [
            'label' => 'Existing Knowledge',
            'route' => 'chatbot.existing',
            'active' => ['chatbot.existing'],
        ];
    }

    $links = [
        [
            'route' => 'sales.index',
            'active' => ['sales.index'],
            'label' => 'Sales Record',
            'icon' => '<path d="M5 17V7m14 10V5m-7 12V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />',
        ],
        [
            'route' => 'stock.index',
            'active' => ['stock.index', 'stock.keys.*'],
            'label' => 'Stock Keys',
            'icon' => '<path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />',
            'children' => $stockChildren,
        ],
        [
            'route' => $isAdmin ? 'chatbot.knowledge' : 'chatbot.simulator',
            'active' => ['chatbot.knowledge', 'chatbot.existing', 'chatbot.simulator'],
            'label' => 'Chatbot',
            'icon' => '<path d="M5 5h14v8H6l-3 3V5z M9 9h6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />',
            'children' => $chatbotChildren,
        ],
    ];

    if ($isAdmin) {
        $links[] = [
            'route' => 'products.index',
            'active' => ['products.index'],
            'label' => 'Products',
            'icon' => '<path d="M4 9h16M4 15h16M9 4v16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />',
        ];



        $links[] = [
            'route' => 'payments.index',
            'active' => ['payments.index', 'payments.balance', 'payments.statements', 'payments.manage', 'payments.withdraw'],
            'label' => 'Billing',
            'icon' => '<path d="M3.5 9h17M6 13h4m-6.5 4h17A1.5 1.5 0 0022 15.5v-7A1.5 1.5 0 0020.5 7h-17A1.5 1.5 0 003.5 17z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />',
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

    <div class="dashboard-nav__heading" style="margin-top: 1.5rem;">
        </svg>
    </div>
    <ul>
        <li class="{{ request()->routeIs('qr.scan') ? 'is-expanded' : '' }}">
            <a
                href="{{ route('qr.scan') }}"
                class="{{ request()->routeIs('qr.scan') ? 'is-active' : '' }}"
                aria-current="{{ request()->routeIs('qr.scan') ? 'page' : 'false' }}"
            >
                <span>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M4 7h3V4H4v3zm13 0h3V4h-3v3zM4 20h3v-3H4v3zm13 0h3v-3h-3v3zM9 4h6v3H9V4zm0 9h6v7H9v-7zm-5-5h3v6H4V8zm13 0h3v6h-3V8z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    QR
                </span>
            </a>
        </li>
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
