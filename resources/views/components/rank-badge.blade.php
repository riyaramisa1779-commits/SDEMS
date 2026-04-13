@props(["rank" => 1, "size" => "sm"])

@php
$colors = [
    1  => "bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300",
    2  => "bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300",
    3  => "bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400",
    4  => "bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400",
    5  => "bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400",
    6  => "bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400",
    7  => "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-500",
    8  => "bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400",
    9  => "bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400",
    10 => "bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400",
];
$labels = [
    1 => "Rank 1", 2 => "Rank 2", 3 => "Rank 3", 4 => "Rank 4",
    5 => "Rank 5", 6 => "Rank 6", 7 => "Rank 7", 8 => "Rank 8",
    9 => "Rank 9", 10 => "Rank 10",
];
$color = $colors[$rank] ?? $colors[1];
$label = $labels[$rank] ?? "Rank {$rank}";
$sizeClass = $size === "xs" ? "px-1.5 py-0.5 text-xs" : "px-2.5 py-0.5 text-xs";
@endphp

<span class="inline-flex items-center gap-1 rounded-full font-semibold {{ $color }} {{ $sizeClass }}">
    @if($rank >= 8)
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
    </svg>
    @endif
    {{ $label }}
</span>
