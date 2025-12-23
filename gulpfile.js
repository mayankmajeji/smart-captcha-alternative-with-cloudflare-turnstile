const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const cleanCSS = require("gulp-clean-css");
const rename = require("gulp-rename");
const path = require("path");
const { exec } = require("child_process");

const scssFiles = ["assets/css/admin.scss", "assets/css/turnstile.scss"];

function styles() {
	return gulp
		.src(scssFiles, { cwd: __dirname })
		.pipe(sass().on("error", sass.logError))
		.pipe(cleanCSS())
		.pipe(
			rename(function (file) {
				file.extname = ".css";
			})
		)
		.pipe(gulp.dest("assets/css"));
}

function migrateScss(cb) {
	let completed = 0;
	const total = scssFiles.length;

	scssFiles.forEach((file) => {
		exec(`npx sass-migrator module --migrate-deps ${file}`, (error, stdout, stderr) => {
			if (error) {
				console.error(`Error migrating ${file}:`, error);
				return cb(error);
			}
			if (stderr) {
				console.log(`Migration output for ${file}:`, stderr);
			}
			completed++;
			if (completed === total) {
				cb();
			}
		});
	});
}

gulp.task("styles", styles);
gulp.task("migrate-scss", migrateScss);
gulp.task("watch", function () {
	gulp.watch(["assets/css/**/*.scss", "assets/css/modules/**/*.scss"], styles);
});

gulp.task("default", gulp.series("styles"));
