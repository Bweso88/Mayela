import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';

import '../../../core/widgets/form_widgets.dart';
import '../../auth/application/session_controller.dart';
import '../../auth/data/models.dart';
import '../../catalog/data/catalog_repository.dart';
import '../../catalog/presentation/grade_picker.dart';

/// Feuille de modification de la classe d'un enfant.
class SchoolingSheet extends ConsumerStatefulWidget {
  const SchoolingSheet({super.key, required this.child});

  final StudentProfile child;

  static Future<void> show(BuildContext context, StudentProfile child) => showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    showDragHandle: true,
    builder: (_) => SchoolingSheet(child: child),
  );

  @override
  ConsumerState<SchoolingSheet> createState() => _SchoolingSheetState();
}

class _SchoolingSheetState extends ConsumerState<SchoolingSheet> with ApiFormState {
  late GradeRef? _grade = widget.child.grade;
  late TrackRef? _track = widget.child.track;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(24, 0, 24, 24 + MediaQuery.viewInsetsOf(context).bottom),
      child: Form(
        key: formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Classe de ${widget.child.firstName}', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 16),
            if (error != null) ...[ErrorBanner(error!.message), const SizedBox(height: 12)],
            GradePicker(
              required: true,
              grade: _grade,
              track: _track,
              gradeError: serverError('grade_id'),
              trackError: serverError('track_id'),
              onChanged: (grade, track) => setState(() {
                _grade = grade;
                _track = track;
              }),
            ),
            const SizedBox(height: 20),
            PrimaryButton(
              label: 'Enregistrer',
              loading: loading,
              onPressed: () => submit(() async {
                final navigator = Navigator.of(context);
                await ref
                    .read(catalogRepositoryProvider)
                    .setChildSchooling(widget.child.id, gradeId: _grade!.id, trackId: _track?.id);
                await ref.read(sessionProvider.notifier).refresh();
                if (mounted) navigator.pop();
              }),
            ),
          ],
        ),
      ),
    );
  }
}
