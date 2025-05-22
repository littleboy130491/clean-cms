@php
    use Illuminate\Support\Str;
    use App\Models\Component;

    $viewName = $view->getName() ?? '';
    $slug = Str::afterLast($viewName, '.');
    $template = Component::where('slug', $slug)->first();
@endphp
