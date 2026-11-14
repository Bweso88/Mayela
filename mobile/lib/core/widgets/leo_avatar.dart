import 'package:material_ui/material_ui.dart';

import '../theme/mayela_colors.dart';

/// Mascotte Léo le lionceau (emoji en attendant les illustrations définitives).
class LeoAvatar extends StatelessWidget {
  const LeoAvatar({super.key, this.size = 72});

  final double size;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: 'Léo le lionceau',
      child: Container(
        width: size,
        height: size,
        alignment: Alignment.center,
        decoration: const BoxDecoration(color: MayelaColors.peach, shape: BoxShape.circle),
        child: Text('🦁', style: TextStyle(fontSize: size * 0.5)),
      ),
    );
  }
}
