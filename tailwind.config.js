/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './app/View/Components/**/*.php',
  ],

  theme: {
    extend: {
      // UCMS soft branding (ইচ্ছা করলে ব্যবহার করবে)
      colors: {
        brand: {
          primary: '#4F46E5',     // indigo-600
          primarySoft: '#EEF2FF', // indigo-50
          accent: '#A855F7',      // purple-500
          accentSoft: '#F3E8FF',  // purple-100
          success: '#059669',     // emerald-600
          successSoft: '#D1FAE5', // emerald-100
          surface: '#F8FAFC',     // slate-50
        },
      },
      borderRadius: {
        'xl': '0.875rem',
        '2xl': '1.25rem',
        '3xl': '1.75rem',
      },
      boxShadow: {
        'card-sm': '0 4px 12px rgba(15, 23, 42, 0.06)',
        'card-md': '0 10px 30px rgba(15, 23, 42, 0.08)',
      },
    },
  },

  /**
   * Safelist: JS template string-এর ভেতরে থাকা class card layout-এর জন্য
   * Tailwind যেন এগুলো purge না করে।
   */
  safelist: [
    // container + layout
    'group',
    'bg-white',
    'rounded-2xl',
    'shadow-sm',
    'border',
    'border-slate-200/70',
    'hover:shadow-lg',
    'hover:border-slate-300',
    'transition-all',
    'duration-300',
    'cursor-pointer',
    'overflow-hidden',
    'h-1.5',
    'w-full',
    'p-6',
    'space-y-5',
    'space-y-2',
    'flex',
    'items-start',
    'items-center',
    'justify-between',
    'gap-4',
    'gap-3',
    'gap-2',
    'gap-1.5',
    'pt-4',
    'border-t',
    'border-slate-100',
    'mt-1',

    // gradient bar
    'bg-gradient-to-r',
    'from-indigo-500',
    'to-purple-600',

    // class code badge
    'w-12',
    'h-12',
    'rounded-xl',
    'bg-indigo-50',
    'border-indigo-200',
    'font-bold',
    'text-indigo-600',
    'text-sm',
    'items-center',
    'justify-center',

    // status pill
    'px-2.5',
    'py-1',
    'rounded-full',
    'text-xs',
    'font-semibold',
    'bg-emerald-100',
    'text-emerald-700',
    'bg-slate-100',
    'text-slate-700',
    'w-2',
    'h-2',
    'bg-emerald-500',
    'rounded-full',

    // title + description
    'text-slate-800',
    'text-lg',
    'leading-snug',
    'group-hover:text-indigo-600',
    'transition-colors',
    'line-clamp-2',
    'text-slate-600',
    'text-sm',
    'leading-relaxed',

    // meta row
    'font-mono',
    'bg-slate-100',
    'px-2',
    'py-1',
    'rounded-lg',
    'text-slate-500',
    'text-xs',

    // footer stats
    'text-slate-700',

    // icons & pill
    'w-4',
    'h-4',
    'px-3',
    'py-1.5',
    'rounded-lg',
  ],

  plugins: [],
}
