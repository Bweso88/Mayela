import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';

import '../../../core/theme/mayela_colors.dart';
import '../../../core/theme/universe.dart';
import '../../../core/widgets/leo_avatar.dart';
import '../../auth/application/session_controller.dart';

/// Accueil élève. Le contenu pédagogique (programme, missions, carte) arrive
/// avec les phases 2 à 4 ; seules les informations réelles du profil sont affichées.
class StudentHomeScreen extends ConsumerWidget {
  const StudentHomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profile = ref.watch(sessionProvider).value?.studentProfile;
    if (profile == null) return const SizedBox.shrink();

    final universe = Universe.fromAge(profile.ageAt(DateTime.now()));
    final isJunior = universe == Universe.junior;
    final text = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(
        title: Text(universe.label),
        actions: [
          IconButton(
            tooltip: 'Se déconnecter',
            icon: const Icon(Icons.logout),
            onPressed: () => ref.read(sessionProvider.notifier).logout(),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: isJunior ? MayelaColors.lavender : MayelaColors.primary,
              borderRadius: BorderRadius.circular(24),
            ),
            child: Row(
              children: [
                if (isJunior) ...[const LeoAvatar(), const SizedBox(width: 16)],
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isJunior ? 'Coucou ${profile.firstName} ! 👋' : 'Bonjour ${profile.firstName} 👋',
                        style: text.headlineMedium?.copyWith(color: isJunior ? MayelaColors.primary : Colors.white),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        isJunior
                            ? 'Léo prépare tes premières missions. Reviens très vite !'
                            : 'Ton programme personnalisé sera bientôt disponible.',
                        style: text.bodyLarge?.copyWith(color: isJunior ? MayelaColors.textPrimary : Colors.white70),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Row(
                children: [
                  const Icon(Icons.star_rounded, color: MayelaColors.star, size: 36),
                  const SizedBox(width: 12),
                  Expanded(child: Text('Niveau ${profile.level}', style: text.titleLarge)),
                  Text('${profile.xp} XP', style: text.titleMedium?.copyWith(color: MayelaColors.secondary)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
