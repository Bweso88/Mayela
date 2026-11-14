import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/mayela_colors.dart';
import '../../auth/application/session_controller.dart';
import '../../auth/data/models.dart';
import 'schooling_sheet.dart';

class ParentHomeScreen extends ConsumerWidget {
  const ParentHomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(sessionProvider).value;
    if (user == null) return const SizedBox.shrink();
    final text = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Espace parent'),
        actions: [
          IconButton(
            tooltip: 'Se déconnecter',
            icon: const Icon(Icons.logout),
            onPressed: () => ref.read(sessionProvider.notifier).logout(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(sessionProvider.notifier).refresh(),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text('Bonjour ${user.name.split(' ').first} 👋', style: text.headlineMedium),
            const SizedBox(height: 4),
            Text(
              'Suivez la progression de vos enfants.',
              style: text.bodyMedium?.copyWith(color: MayelaColors.textSecondary),
            ),
            const SizedBox(height: 24),
            Text('Mes enfants', style: text.titleLarge),
            const SizedBox(height: 12),
            if (user.children.isEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Text(
                    'Aucun enfant pour le moment. Ajoutez votre premier enfant pour lui créer son code de connexion.',
                    style: text.bodyMedium,
                  ),
                ),
              ),
            for (final child in user.children) ...[_ChildCard(child: child), const SizedBox(height: 12)],
            const SizedBox(height: 8),
            FilledButton.icon(
              onPressed: () => context.push('/parent/children/new'),
              icon: const Icon(Icons.person_add_alt_1),
              label: const Text('Ajouter un enfant'),
            ),
          ],
        ),
      ),
    );
  }
}

class _ChildCard extends StatelessWidget {
  const _ChildCard({required this.child});

  final StudentProfile child;

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            CircleAvatar(
              radius: 26,
              backgroundColor: MayelaColors.lavender,
              child: Text(
                child.firstName.characters.first.toUpperCase(),
                style: const TextStyle(color: MayelaColors.primary, fontWeight: FontWeight.w700, fontSize: 20),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '${child.firstName} ${child.lastName}',
                    style: text.titleMedium?.copyWith(fontWeight: FontWeight.w700),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${child.ageAt(DateTime.now())} ans · ${child.schoolingLabel ?? 'Classe à définir'}',
                    style: text.bodySmall,
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(color: MayelaColors.mint, borderRadius: BorderRadius.circular(20)),
                    child: Text(
                      'Code : ${child.loginCode}',
                      style: const TextStyle(color: MayelaColors.onMint, fontWeight: FontWeight.w700, letterSpacing: 1),
                    ),
                  ),
                ],
              ),
            ),
            IconButton(
              tooltip: 'Modifier la classe',
              icon: const Icon(Icons.school_outlined),
              onPressed: () => SchoolingSheet.show(context, child),
            ),
            IconButton(
              tooltip: 'Copier le code',
              icon: const Icon(Icons.copy_rounded),
              onPressed: () {
                Clipboard.setData(ClipboardData(text: child.loginCode));
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Code copié')));
              },
            ),
          ],
        ),
      ),
    );
  }
}
