import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';

import '../../../core/theme/mayela_colors.dart';
import '../../../core/widgets/leo_avatar.dart';

class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Scaffold(
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: 32),
            const Center(child: LeoAvatar(size: 96)),
            const SizedBox(height: 20),
            Text(
              'Mayela',
              textAlign: TextAlign.center,
              style: text.displaySmall?.copyWith(color: MayelaColors.primary, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 8),
            Text(
              'Ton compagnon d\'apprentissage, du CE1 à la Terminale.',
              textAlign: TextAlign.center,
              style: text.bodyLarge?.copyWith(color: MayelaColors.textSecondary),
            ),
            const SizedBox(height: 48),
            FilledButton.icon(
              onPressed: () => context.push('/login/student'),
              icon: const Icon(Icons.backpack_outlined),
              label: const Text('Je suis élève (code + PIN)'),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () => context.push('/login'),
              icon: const Icon(Icons.mail_outline),
              label: const Text('Connexion e-mail ou téléphone'),
            ),
            const SizedBox(height: 32),
            TextButton(
              onPressed: () => context.push('/register/parent'),
              child: const Text('Parent ? Créer un compte famille'),
            ),
          ],
        ),
      ),
    );
  }
}
