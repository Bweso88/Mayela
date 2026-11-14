import 'package:material_ui/material_ui.dart';

import '../api/api_exception.dart';
import '../theme/mayela_colors.dart';

/// Bandeau d'erreur affiché en haut d'un formulaire.
class ErrorBanner extends StatelessWidget {
  const ErrorBanner(this.message, {super.key});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      liveRegion: true,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(color: MayelaColors.errorContainer, borderRadius: BorderRadius.circular(14)),
        child: Row(
          children: [
            const Icon(Icons.error_outline, color: MayelaColors.error),
            const SizedBox(width: 10),
            Expanded(
              child: Text(message, style: const TextStyle(color: MayelaColors.error)),
            ),
          ],
        ),
      ),
    );
  }
}

/// Bouton principal avec indicateur de chargement.
class PrimaryButton extends StatelessWidget {
  const PrimaryButton({super.key, required this.label, required this.onPressed, this.loading = false});

  final String label;
  final VoidCallback? onPressed;
  final bool loading;

  @override
  Widget build(BuildContext context) {
    return FilledButton(
      onPressed: loading ? null : onPressed,
      child: loading
          ? const SizedBox(
              width: 22,
              height: 22,
              child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
            )
          : Text(label),
    );
  }
}

/// Mixin de gestion d'état commun aux formulaires qui appellent l'API.
mixin ApiFormState<T extends StatefulWidget> on State<T> {
  final formKey = GlobalKey<FormState>();
  bool loading = false;
  ApiException? error;

  String? serverError(String field) => error?.firstErrorFor(field);

  Future<void> submit(Future<void> Function() action) async {
    setState(() => error = null);
    if (!(formKey.currentState?.validate() ?? false)) return;
    setState(() => loading = true);
    try {
      await action();
    } on ApiException catch (e) {
      if (mounted) setState(() => error = e);
      formKey.currentState?.validate(); // Affiche les erreurs de champs renvoyées par le serveur.
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }
}

abstract final class Validators {
  static String? required(String? v, [String label = 'Ce champ']) =>
      (v == null || v.trim().isEmpty) ? '$label est obligatoire.' : null;

  static String? pin(String? v) =>
      (v == null || !RegExp(r'^\d{4,6}$').hasMatch(v)) ? 'Le PIN contient 4 à 6 chiffres.' : null;

  static String? password(String? v) {
    if (v == null || v.length < 8) return 'Au moins 8 caractères.';
    if (!RegExp(r'[A-Za-z]').hasMatch(v) || !RegExp(r'\d').hasMatch(v)) {
      return 'Au moins une lettre et un chiffre.';
    }
    return null;
  }
}
