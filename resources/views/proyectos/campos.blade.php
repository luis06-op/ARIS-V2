<div>
    <label class="aris-etiqueta" for="title">Título del proyecto</label>
    <input class="aris-campo" id="title" name="title" value="{{ old('title', $proyecto->title ?? '') }}" maxlength="180" required>
</div>
<div>
    <label class="aris-etiqueta" for="description">Descripción</label>
    <textarea class="aris-campo" id="description" name="description" rows="6" maxlength="10000">{{ old('description', $proyecto->description ?? '') }}</textarea>
</div>
