import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';

import '../../auth/data/models.dart';
import '../data/catalog_repository.dart';

/// Choix d'une classe puis, si la classe en propose, d'une série.
class GradePicker extends ConsumerWidget {
  const GradePicker({
    super.key,
    required this.grade,
    required this.track,
    required this.onChanged,
    this.required = false,
    this.gradeError,
    this.trackError,
  });

  final GradeRef? grade;
  final TrackRef? track;
  final void Function(GradeRef? grade, TrackRef? track) onChanged;
  final bool required;
  final String? gradeError;
  final String? trackError;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final grades = ref.watch(gradesProvider);

    return grades.when(
      loading: () => const LinearProgressIndicator(),
      error: (e, _) => Row(
        children: [
          const Expanded(child: Text('Impossible de charger la liste des classes.')),
          TextButton(onPressed: () => ref.invalidate(gradesProvider), child: const Text('Réessayer')),
        ],
      ),
      data: (items) => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          DropdownButtonFormField<int>(
            key: const Key('grade-picker'),
            initialValue: grade?.id,
            isExpanded: true,
            decoration: InputDecoration(labelText: required ? 'Classe' : 'Classe (facultatif)'),
            items: [for (final g in items) DropdownMenuItem(value: g.id, child: Text(g.label))],
            onChanged: (id) => onChanged(items.where((g) => g.id == id).firstOrNull, null),
            validator: (id) => (required && id == null) ? 'Choisis une classe.' : gradeError,
          ),
          if (grade?.requiresTrack ?? false) ...[
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              key: const Key('track-picker'),
              initialValue: track?.id,
              decoration: const InputDecoration(labelText: 'Série'),
              items: [for (final t in grade!.tracks) DropdownMenuItem(value: t.id, child: Text(t.name))],
              onChanged: (id) => onChanged(grade, grade!.tracks.where((t) => t.id == id).firstOrNull),
              validator: (id) => id == null ? 'Choisis une série.' : trackError,
            ),
          ],
        ],
      ),
    );
  }
}
