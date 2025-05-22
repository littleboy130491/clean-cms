@php
    $filePathName = 'dynamic';
    $componentName = $filePathName . $name;
@endphp
@dd($componentData)
@if ($componentData)
    <x-dynamic-component :component="'components.' . $componentName" :componentData="$componentData" />
@else
    <!-- Optionally, show a fallback -->
    <div>Component not found.</div>
@endif
