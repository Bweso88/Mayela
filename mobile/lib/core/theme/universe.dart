/// Les trois univers visuels de Mayela.
enum Universe {
  /// CE1 → CM2 : aventure gamifiée avec Léo.
  junior,

  /// 6e → 3e.
  college,

  /// Seconde → Terminale.
  lycee;

  /// Choix provisoire selon l'âge, tant que la classe n'est pas renseignée
  /// (la classe et son cycle arrivent en phase 2 et deviendront la référence).
  static Universe fromAge(int age) {
    if (age < 11) return Universe.junior;
    if (age < 15) return Universe.college;
    return Universe.lycee;
  }

  String get label => switch (this) {
    Universe.junior => 'Mayela Junior',
    Universe.college => 'Mayela Collège',
    Universe.lycee => 'Mayela Lycée',
  };
}
